<?php

namespace App\Services;

use Illuminate\Support\Str;
use App\Exceptions\ExclusiveLockException;
use App\Models\AgencyWithdrawalItemHistory;
use App\Repositories\AccountPayableItem\AccountPayableItemRepository;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\AgencyWithdrawalItemHistory\AgencyWithdrawalItemHistoryRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\AgencyWithdrawalItemHistoryCustomValueService;
use App\Services\AccountPayableItemService;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use App\Traits\UserCustomItemTrait;

class AgencyWithdrawalItemHistoryService
{
    use UserCustomItemTrait;

    public function __construct(AgencyRepository $agencyRepository, AgencyWithdrawalItemHistoryRepository $agencyWithdrawalItemHistoryRepository, AccountPayableItemRepository $accountPayableItemRepository, AgencyWithdrawalItemHistoryCustomValueService $agencyWithdrawalItemHistoryCustomValueService, AccountPayableItemService $accountPayableItemService)
    {
        $this->agencyRepository = $agencyRepository;
        $this->agencyWithdrawalItemHistoryRepository = $agencyWithdrawalItemHistoryRepository;
        $this->accountPayableItemRepository = $accountPayableItemRepository;
        $this->agencyWithdrawalItemHistoryCustomValueService = $agencyWithdrawalItemHistoryCustomValueService;
        $this->accountPayableItemService = $accountPayableItemService;
    }

    // /**
    //  * 該当IDを一件取得
    //  *
    //  * @param int $id ID
    //  * @param array $select 取得カラム
    //  */
    // public function find(int $id, array $with = [], array $select=[]) : ?AgencyWithdrawalItemHistory
    // {
    //     return $this->agencyWithdrawalItemHistoryRepository->find($id, $with, $select);
    // }

    // /**
    //  * 当該予約の出金額合計を取得
    //  * 行ロックで取得
    //  *
    //  * @param int $reserveId 予約ID
    //  * @return int
    //  */
    // public function getSumAmountByReserveId(int $reserveId, bool $isLock=false) : int
    // {
    //     return $this->agencyWithdrawalItemHistoryRepository->getSumAmountByReserveId($reserveId, $isLock);
    // }

    // /**
    //  * 当該支払い明細の出金額合計を取得
    //  * 行ロックで取得
    //  *
    //  * @param int $accountPayableItemId 支払い明細ID
    //  * @return int
    //  */
    // public function getSumAmountByAccountPayableItemId(int $accountPayableItemId, bool $isLock=false) : int
    // {
    //     return $this->agencyWithdrawalItemHistoryRepository->getSumAmountByAccountPayableItemId($accountPayableItemId, $isLock);
    // }

    /**
     * 出金登録
     *
     * @throws ExclusiveLockException 同時編集を検知した場合は例外を投げる
     * @param bool $checkUpdatedAt account_payable_detailsが更新されているかチェックする場合はTrue
     */
    public function create(array $data, bool $checkAccountPayableItemUpdatedAt = true): AgencyWithdrawalItemHistory
    {
        if ($checkAccountPayableItemUpdatedAt) {
            $accountPayableItem = $this->accountPayableItemRepository->find((int)$data['account_payable_item_id']);
            if ($accountPayableItem->updated_at != Arr::get($data, 'account_payable_item.updated_at')) {
                throw new ExclusiveLockException;
            }
        }

        // 出金IDを生成
        $withdrawalKey = $this->generateWithdrawalKey();

        $data['withdrawal_key'] = $withdrawalKey;

        $agencyWithdrawalItemHistory = $this->agencyWithdrawalItemHistoryRepository->create($data);

        $customFields = $this->customFieldsExtraction($data); // 入力データからカスタムフィールドを抽出
        if ($customFields) {
            $this->agencyWithdrawalItemHistoryCustomValueService->upsertCustomFileds($customFields, $agencyWithdrawalItemHistory->id); // カスタムフィールド保存
        }

        $this->accountPayableItemService->updateFields($agencyWithdrawalItemHistory->account_payable_item_id, [
            'last_manager_id' => $agencyWithdrawalItemHistory->manager_id,
            'last_note' => $agencyWithdrawalItemHistory->note
        ]); // account_payable_detailsテーブルの担当者と備考を更新

        return $agencyWithdrawalItemHistory;
    }

    // /**
    //  * 当該予約IDに紐づく一覧を取得
    //  */
    // public function getByReserveId(int $reserveId, array $with = [], array $select=[]) : Collection
    // {
    //     return $this->agencyWithdrawalItemHistoryRepository->getWhere(['reserve_id' => $reserveId], $with, $select);
    // }

    // /**
    //  * 当該参加者に紐づく出金情報がある場合はtrue
    //  */
    // public function isExistsParticipant(int $participantId, int $reserveId) : bool
    // {
    //     return $this->agencyWithdrawalItemHistoryRepository->isExistsParticipant($participantId, $reserveId);
    // }

    // /**
    //  * 削除
    //  *
    //  * @param int $id ID
    //  * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
    //  */
    // public function delete(int $id, bool $isSoftDelete=true): bool
    // {
    //     return $this->agencyWithdrawalItemHistoryRepository->delete($id, $isSoftDelete);
    // }

    /**
     * 出金IDを生成
     */
    private function generateWithdrawalKey()
    {
        return (string)Str::uuid();
    }
}
