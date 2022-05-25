<?php

namespace App\Services;

use App\Exceptions\ExclusiveLockException;
use App\Models\AccountPayableItem;
use App\Models\AgencyWithdrawal;
use App\Repositories\AccountPayableDetail\AccountPayableDetailRepository;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\AgencyWithdrawal\AgencyWithdrawalRepository;
use App\Repositories\UserCustomItem\UserCustomItemRepository;
use App\Services\AccountPayableDetailService;
use App\Services\AgencyWithdrawalCustomValueService;
use App\Traits\UserCustomItemTrait;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class AgencyWithdrawalService
{
    use UserCustomItemTrait;

    public function __construct(AgencyRepository $agencyRepository, AgencyWithdrawalRepository $agencyWithdrawalRepository, AccountPayableDetailRepository $accountPayableDetailRepository, AgencyWithdrawalCustomValueService $agencyWithdrawalCustomValueService, AccountPayableDetailService $accountPayableDetailService, UserCustomItemRepository $userCustomItemRepository)
    {
        $this->agencyRepository = $agencyRepository;
        $this->agencyWithdrawalRepository = $agencyWithdrawalRepository;
        $this->accountPayableDetailRepository = $accountPayableDetailRepository;
        $this->agencyWithdrawalCustomValueService = $agencyWithdrawalCustomValueService;
        $this->accountPayableDetailService = $accountPayableDetailService;
        $this->userCustomItemRepository = $userCustomItemRepository;
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
     *
     * @param string $bulkWithdrawalKey 一括出金識別キー
     * @param ?string $applicationStep 申し込み段階。全レコード対象の場合はnull
     * @param bool $exZero 仕入額・未払い額が0円のレコードを取得しない場合はtrue
     */
    public function bulkCreateForItem(array $input, string $bulkWithdrawalKey, AccountPayableItem $accountPayableItem, ?string $applicationStep)
    {
        /******************** agency_withdrawalsテーブルへの出金登録  ********************/

        $insertParams = []; // agency_withdrawalsテーブルにinsertする値をセット

        // 作業用の一時IDを発行。あとで本IDを持つレコードに対してカスタム項目を一括登録するため
        $tmpId = $this->generateTempId();


        $withdrawalTotal = 0; // 出金合計。amountの値と等しいか検算するために使用
        
        $amount = Arr::get($input, "amount", 0);
        if ($amount == 0) {
            throw new \Exception("出金額を入力してください。");
        }

        // 出金対象となる商品仕入れコードをselectし、出金レコード作成用の配列を作成
        $query = $this->accountPayableDetailService
                    ->getSummarizeItemQuery($accountPayableItem->toArray(), true) // 行ロック
                    ->select(['id', 'unpaid_balance', 'reserve_travel_date_id', 'saleable_type', 'saleable_id', 'supplier_id'])
                    ->with(['saleable:id,participant_id']); 
    
        $query = $applicationStep === config('consts.reserves.APPLICATION_STEP_RESERVE') ? $query->decided() : $query;
    
        if($amount > 0){ // 通常出金
            
            if ($accountPayableItem->total_amount_accrued == 0) {
                throw new \Exception("未払金額はありません。");
            }

            // 出金割合
            $rate = get_agency_withdrawal_rate($amount, $accountPayableItem->total_amount_accrued); // 1, 0.5 ...etc

            // 未払金額があるレコードが対象
            $query = $query->where('unpaid_balance', '>', 0);


        } else { // マイナス出金(過払金処理)

            if ($accountPayableItem->total_overpayment == 0) {
                throw new \Exception("過払金額はありません。");
            }

            // 出金割合
            $rate = get_agency_withdrawal_rate($amount, $accountPayableItem->total_overpayment); // 1, 0.5 ...etc

            // 過払金額があるレコードが対象
            $query = $query->where('unpaid_balance', '<', 0);

        }

        $query->chunk(100, function ($rows) use (&$insertParams, $input, $bulkWithdrawalKey, $rate, $tmpId, &$withdrawalTotal) { // 負荷対策のため念の為、100件ずつ取得
            foreach ($rows as $row) {
                $tmp = [];
                /** 全レコード共通値 */
                $tmp['agency_id'] = Arr::get($input, "agency_id");
                $tmp['reserve_id'] = Arr::get($input, "reserve_id");
                $tmp['withdrawal_date'] = Arr::get($input, "withdrawal_date");
                $tmp['record_date'] = Arr::get($input, "record_date");
                $tmp['manager_id'] = Arr::get($input, "manager_id");
                $tmp['note'] = null; // 備考は一括入力したものをセットする必要はないと思われる
                $tmp['supplier_id_log'] = $row->supplier_id;
                $tmp['bulk_withdrawal_key'] = $bulkWithdrawalKey;
                $tmp['temp_id'] = $tmpId;
                /** レコードによって変わる値 */
                $amnt = $row->unpaid_balance * $rate;
                if (!preg_match('/^[\-0-9]+$/', $amnt)) { // 商品仕入額のパーセンテージが割り切れないケース(過払金処理の場合はマイナスなのでマイナス符号もパターンに含む)
                    throw new \Exception("未払金額に対する出金額の割合が正しくありません。");
                }
                $tmp['amount'] = $amnt;
                $tmp['account_payable_detail_id'] = $row->id;
                $tmp['reserve_travel_date_id'] = $row->reserve_travel_date_id;
                $tmp['participant_id'] = $row->saleable->participant_id;

                $insertParams[] = $tmp;
                $withdrawalTotal += $amnt; // 検算用の合計金額を足し込み
            }
        });

        if ($withdrawalTotal != $amount) {
            throw new \Exception("出金額の合計が正しくありません。");
        }

        foreach (array_chunk($insertParams, 1000) as $rows) { // 念の為、1,000ずつの配列に分けてinsert
            $this->agencyWithdrawalRepository->insert($rows); // 出金レコードをバルクインサート
        }

        /******************** account_payable_detailsレコードの最終担当者の更新  ********************/

        $updateParams = []; // account_payable_detailsレコードで更新する値をセット(最終担者IDを更新する)
        foreach ($insertParams as $row) { // insertParamsに保存したaccount_payable_detail_id,manager_idを抽出
            $tmp = [];
            $tmp['id'] = Arr::get($row, 'account_payable_detail_id');
            $tmp['last_manager_id'] = Arr::get($row, 'manager_id');
            $updateParams[] = $tmp;
        }

        foreach (array_chunk($updateParams, 1000) as $rows) { // 念の為、1,000ずつの配列に分けてupdate
            $this->accountPayableDetailService->updateBulk($updateParams, "id");
        }

        /******************** カスタム項目の一括登録  ********************/

        /**
         * 入力データからカスタムフィールドを抽出
         * ↓
         * 出金登録した行をtemp_idを条件にid一覧を取得
         * ↓
         * カスタム項目を負荷対策のため1,000行ずつバルクインサート
         */

        $customFields = $this->customFieldsExtraction($input); // 入力データからカスタムフィールドを抽出
        if ($customFields) {
            $agencyWithdrawalIds = $this->agencyWithdrawalRepository->getIdsByTempId($tmpId); // 一括登録した出金ID一覧

            // バルクインサート用配列
            $insertRows = [];

            $userCustomItems = $this->userCustomItemRepository->getByKeys(array_keys($customFields, ), [], ['id','key']);
            
            foreach ($agencyWithdrawalIds as $id) {
                foreach ($userCustomItems as $uci) {
                    $tmp = [];
                    $tmp['agency_withdrawal_id'] = $id;
                    $tmp['user_custom_item_id'] = $uci->id;
                    $tmp['val'] = Arr::get($customFields, $uci->key);

                    $insertRows[] = $tmp;
                }
            }

            foreach (array_chunk($insertRows, 1000) as $rows) { // 念の為、1,000ずつの配列に分けてinsert
                $this->agencyWithdrawalCustomValueService->insert($rows); // バルクインサート
            }
        }
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
     * 作業用一時IDを生成
     */
    public function generateTempId()
    {
        return uniqid(mt_rand());
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

    /**
     * 当該一括出金識別キーのレコードを削除
     *
     * @param string $bulkWithdrawalKey 一括出金識別キー
     * @return boolean
     */
    public function deleteByBulkWithdrawalKey(string $bulkWithdrawalKey, bool $isSoftDelete=true)
    {
        return $this->agencyWithdrawalRepository->deleteWhere(['bulk_withdrawal_key' => $bulkWithdrawalKey], $isSoftDelete);
    }

    /**
     * 復元
     * 
     * @param int $id
     * @return boolean
     */
    public function restore(int $id)
    {
        $this->agencyWithdrawalRepository->restore($id);
    }
}
