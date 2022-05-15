<?php

namespace App\Services;

use App\Exceptions\ExclusiveLockException;
use App\Models\AccountPayableItem;
use App\Models\AgencyWithdrawal;
use App\Repositories\AccountPayableDetail\AccountPayableDetailRepository;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\AgencyWithdrawal\AgencyWithdrawalRepository;
use App\Services\AccountPayableDetailService;
use App\Services\AgencyWithdrawalCustomValueService;
use App\Traits\UserCustomItemTrait;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class AgencyWithdrawalService
{
    use UserCustomItemTrait;

    public function __construct(AgencyRepository $agencyRepository, AgencyWithdrawalRepository $agencyWithdrawalRepository, AccountPayableDetailRepository $accountPayableDetailRepository, AgencyWithdrawalCustomValueService $agencyWithdrawalCustomValueService, AccountPayableDetailService $accountPayableDetailService)
    {
        $this->agencyRepository = $agencyRepository;
        $this->agencyWithdrawalRepository = $agencyWithdrawalRepository;
        $this->accountPayableDetailRepository = $accountPayableDetailRepository;
        $this->agencyWithdrawalCustomValueService = $agencyWithdrawalCustomValueService;
        $this->accountPayableDetailService = $accountPayableDetailService;
    }

    /**
     * 該当IDを一件取得
     *
     * @param int $id ID
     * @param array $select 取得カラム
     */
    public function find(int $id, array $with = [], array $select=[]) : ?AgencyWithdrawal
    {
        return $this->agencyWithdrawalRepository->find($id, $with, $select);
    }

    /**
     * 当該予約の出金額合計を取得
     * 行ロックで取得
     *
     * @param int $reserveId 予約ID
     * @return int
     */
    public function getSumAmountByReserveId(int $reserveId, bool $isLock=false) : int
    {
        return $this->agencyWithdrawalRepository->getSumAmountByReserveId($reserveId, $isLock);
    }

    /**
     * 当該支払い明細の出金額合計を取得
     * 行ロックで取得
     *
     * @param int $accountPayableDetailId 支払い明細ID
     * @return int
     */
    public function getSumAmountByAccountPayableDetailId(int $accountPayableDetailId, bool $isLock=false) : int
    {
        return $this->agencyWithdrawalRepository->getSumAmountByAccountPayableDetailId($accountPayableDetailId, $isLock);
    }

    /**
     * 出金登録
     *
     * @throws ExclusiveLockException 同時編集を検知した場合は例外を投げる
     * @param bool $checkUpdatedAt account_payable_detailsが更新されているかチェックする場合はTrue
     */
    public function create(array $data, bool $checkAccountPayableDetailUpdatedAt = true): AgencyWithdrawal
    {
        if ($checkAccountPayableDetailUpdatedAt) {
            $accountPayableDetail = $this->accountPayableDetailRepository->find((int)$data['account_payable_detail_id']);
            if ($accountPayableDetail->updated_at != Arr::get($data, 'account_payable_detail.updated_at')) {
                throw new ExclusiveLockException;
            }
        }

        $agencyWithdrawal = $this->agencyWithdrawalRepository->create($data);

        $customFields = $this->customFieldsExtraction($data); // 入力データからカスタムフィールドを抽出
        if ($customFields) {
            $this->agencyWithdrawalCustomValueService->upsertCustomFileds($customFields, $agencyWithdrawal->id); // カスタムフィールド保存
        }

        $this->accountPayableDetailService->updateFields($agencyWithdrawal->account_payable_detail_id, [
            'last_manager_id' => $agencyWithdrawal->manager_id,
            'last_note' => $agencyWithdrawal->note
        ]); // account_payable_detailsテーブルの担当者と備考を更新

        return $agencyWithdrawal;
    }

    /**
     * バルクインサート(支払管理の仕入先＆商品毎ページ用出金登録)
     * クエリの組み立ては、基本的にAccountPayableDetailRepository@paginateByAgencyIdと同じ
     *
     * @param ?string $applicationStep 申し込み段階。全レコード対象の場合はnull
     * @param bool $exZero 仕入額・未払い額が0円のレコードを取得しない場合はtrue
     */
    public function bulkCreateForItem(array $input, AccountPayableItem $accountPayableItem, ?string $applicationStep, bool $exZero = true)
    {
        $insertParams = [];

        // 出金割合
        $rate = get_agency_withdrawal_rate(Arr::get($input, "amount"), $accountPayableItem->unpaid_balance);

        // 出金対象となる商品仕入れコードをselectし、出金レコード作成用の配列を作成
        $query = $applicationStep === config('consts.reserves.APPLICATION_STEP_RESERVE') ? $this->accountPayableDetailService->getSummarizeItemQuery($accountPayableItem->toArray())->decided()->select(['id', 'unpaid_balance', 'reserve_travel_date_id', 'saleable_type', 'saleable_id'])->with(['saleable:id,participant_id']) : $this->accountPayableDetailService->getSummarizeItemQuery($accountPayableItem->toArray())->select(['id', 'unpaid_balance', 'reserve_travel_date_id', 'saleable_type', 'saleable_id'])->with(['saleable:id,participant_id']); // スコープを設定

        // 入額・未払い額が0円のレコードを取得しない場合はexcludingzero
        $query = $exZero ? $query->excludingzero() : $query;

        $query->chunk(100, function ($rows) use (&$insertParams, $input, $rate) { // 負荷対策のため念の為、100件ずつ取得
            foreach ($rows as $row) {
                $tmp = [];
                /** 全レコード共通値 */
                $tmp['agency_id'] = Arr::get($input, "agency_id");
                $tmp['reserve_id'] = Arr::get($input, "reserve_id");
                $tmp['withdrawal_date'] = Arr::get($input, "withdrawal_date");
                $tmp['record_date'] = Arr::get($input, "record_date");
                $tmp['manager_id'] = Arr::get($input, "manager_id");
                $tmp['note'] = null; // 備考は一括入力したものをセットする必要はないとお思われる
                $tmp['supplier_id_log'] = Arr::get($input, "manager_id");
                /** レコードによって変わる値 */
                $amount = $row->unpaid_balance * $rate;
                if (!preg_match('/^[0-9\-]+$/', $amount)) { // 商品仕入額のパーセンテージが割り切れないケース。数字とマイナス記号のみの構成であること(マイナス記号は必要か不明だが、一応許可しておく)
                    throw new \Exception("未払金額に対する出金額の割合が正しくありません。");
                }
                $tmp['amount'] = $amount;
                $tmp['account_payable_detail_id'] = $row->id;
                $tmp['reserve_travel_date_id'] = $row->reserve_travel_date_id;
                $tmp['participant_id'] = $row->saleable->participant_id;

                $insertParams[] = $tmp;
            }
        });

        // 出金レコードをバルクインサート
        return $this->agencyWithdrawalRepository->insert($insertParams);
    }

    /**
     * 当該予約IDに紐づく一覧を取得
     */
    public function getByReserveId(int $reserveId, array $with = [], array $select=[]) : Collection
    {
        return $this->agencyWithdrawalRepository->getWhere(['reserve_id' => $reserveId], $with, $select);
    }

    /**
     * 当該参加者に紐づく出金情報がある場合はtrue
     */
    public function isExistsParticipant(int $participantId, int $reserveId) : bool
    {
        return $this->agencyWithdrawalRepository->isExistsParticipant($participantId, $reserveId);
    }

    /**
     * 削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
     */
    public function delete(int $id, bool $isSoftDelete=true): bool
    {
        return $this->agencyWithdrawalRepository->delete($id, $isSoftDelete);
    }
}
