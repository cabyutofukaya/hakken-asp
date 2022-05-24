<?php

namespace App\Services;

use Illuminate\Support\Str;
use App\Exceptions\ExclusiveLockException;
use App\Models\AgencyWithdrawal;
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

    /**
     * 該当IDを一件取得
     *
     * @param int $id ID
     * @param array $select 取得カラム
     */
    public function find(int $id, array $with = [], array $select=[]) : ?AgencyWithdrawalItemHistory
    {
        return $this->agencyWithdrawalItemHistoryRepository->find($id, $with, $select);
    }

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

    /**
     * 個別出金の出金情報を記録(仕入管理の第3階層で出金登録した内容を第2階層へ記録)
     * 出力しやすくするためカスタム項目もagency_withdrawal_item_historiesにコピー
     *
     * @param AgencyWithdrawal $agencyWithdrawal
     */
    public function setIndividualWithdrawal(AgencyWithdrawal $agencyWithdrawal) : AgencyWithdrawalItemHistory
    {
        // 保存データを作成
        $data = [];
        $data['account_payable_item_id'] = $this->accountPayableItemService->getIdByAccountPayableDetail($agencyWithdrawal->account_payable_detail);
        $data['agency_id'] = $agencyWithdrawal->agency_id;
        $data['reserve_id'] = $agencyWithdrawal->reserve_id;
        $data['agency_withdrawal_id'] = $agencyWithdrawal->id;
        $data['payment_type'] = config('consts.agency_withdrawal_item_histories.PAYMENT_TYPE_INDIVIDUAL'); // 個別出金ステータス
        $data['amount'] = $agencyWithdrawal->amount; // 出金額
        $data['manager_id'] = $agencyWithdrawal->manager_id;
        $data['bulk_withdrawal_key'] = null;
        $data['withdrawal_date'] = $agencyWithdrawal->withdrawal_date; // 出金日
        $data['record_date'] = $agencyWithdrawal->record_date; // 登録日
        $data['note'] = $agencyWithdrawal->note;

        $agencyWithdrawalItemHistory = $this->agencyWithdrawalItemHistoryRepository->create($data);

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

    /**
     * 削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
     */
    public function delete(int $id, bool $isSoftDelete=true): bool
    {
        return $this->agencyWithdrawalItemHistoryRepository->delete($id, $isSoftDelete);
    }

    /**
     * 当該 agency_withdrawal_idのレコードを削除
     *
     * @param int $agencyWithdrawalId
     * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
     */
    public function deleteByAgencyWithdrawalId(int $agencyWithdrawalId, bool $isSoftDelete=true)
    {
        return $this->agencyWithdrawalItemHistoryRepository->deleteWhere(['agency_withdrawal_id' => $agencyWithdrawalId], $isSoftDelete);
    }

    /**
     * 一括出金識別キーを生成
     *
     * @return string
     */
    public function generateWithdrawalKey()
    {
        return md5(uniqid(mt_rand(), true));
    }
}
