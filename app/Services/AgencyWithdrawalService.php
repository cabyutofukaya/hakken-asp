<?php

namespace App\Services;

use App\Exceptions\ExclusiveLockException;
use App\Models\AgencyWithdrawal;
use App\Repositories\AccountPayableDetail\AccountPayableDetailRepository;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\AgencyWithdrawal\AgencyWithdrawalRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\AgencyWithdrawalCustomValueService;
use App\Services\AccountPayableDetailService;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use App\Traits\UserCustomItemTrait;

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
