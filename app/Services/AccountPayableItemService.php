<?php

namespace App\Services;

use App\Exceptions\ExclusiveLockException;
use App\Models\AccountPayableDetail;
use App\Models\AccountPayableItem;
use App\Repositories\AccountPayableItem\AccountPayableItemRepository;
use App\Repositories\Agency\AgencyRepository;
use App\Services\AccountPayableDetailService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class AccountPayableItemService
{
    public function __construct(AgencyRepository $agencyRepository, AccountPayableItemRepository $accountPayableItemRepository, AccountPayableDetailService $accountPayableDetailService)
    {
        $this->agencyRepository = $agencyRepository;
        $this->accountPayableItemRepository = $accountPayableItemRepository;
        $this->accountPayableDetailService = $accountPayableDetailService;
    }

    /**
     * 該当IDを一件取得
     *
     * @param int $id ID
     * @param array $select 取得カラム
     * @param bool $isLock 行ロックして取得する場合はtrue
     */
    public function find(int $id, array $with = [], array $select=[], bool $isLock = false) : ?AccountPayableItem
    {
        return $this->accountPayableItemRepository->find($id, $with, $select, $isLock);
    }

    /**
     * 当該行程IDのNet・未払金額を更新
     *
     * @param int $reserveItineraryId 行程ID
     */
    public function refreshAmountByReserveItineraryId(?int $reserveItineraryId)
    {
        $this->accountPayableItemRepository->refreshAmountByReserveItineraryId($reserveItineraryId);
    }

    /**
     * 一覧を取得
     *
     * @param string $agencyAccount
     * @param int $applicationStep 予約段階（見積/予約）
     * @param int $limit
     * @param array $with
     * @param bool $exZero 仕入額・未払い額が0円のレコードを取得しない場合はtrue
     * @param
     */
    public function paginateByAgencyAccount(string $agencyAccount, array $params, int $limit, ?string $applicationStep = null, array $with = [], array $select=[], bool $exZero = true) : LengthAwarePaginator
    {
        $agencyId = $this->agencyRepository->getIdByAccount($agencyAccount);
        return $this->accountPayableItemRepository->paginateByAgencyId($agencyId, $params, $limit, $applicationStep, $with, $select, $exZero);
    }

    /**
     * 任意のフィールドを更新
     *
     * @param int $id account_payable_items ID
     */
    public function updateFields(int $id, $params)
    {
        return $this->accountPayableItemRepository->updateField($id, $params);
    }

    /**
     * 支払日を更新
     *
     */
    public function paymentDateUpdate(int $id, string $paymentDate): AccountPayableItem
    {
        $accountPayableItem = $this->accountPayableItemRepository->update($id, ['payment_date' => $paymentDate]);

        // 関連するaccount_payable_detailsレコードの利用日を更新
        $this->accountPayableDetailService->updatePaymentDate($accountPayableItem->reserve_id, $accountPayableItem->supplier_id, $paymentDate);

        return $accountPayableItem;
    }

    /**
     * 更新
     *
     * @throws ExclusiveLockException 同時編集を検知した場合は例外を投げる
     */
    public function update(int $id, array $data): AccountPayableItem
    {
        $accountPayableItem = $this->accountPayableItemRepository->find($id);
        if ($accountPayableItem->updated_at != Arr::get($data, 'updated_at')) {
            throw new ExclusiveLockException;
        }

        return $this->accountPayableItemRepository->update($id, $data);
    }

    /**
     * 当該支払先を除く行程行を削除
     *
     * @param int $reserveItineraryId 行程ID
     * @param array $supplierIds 仕入先ID一覧。当リストに含まれる仕入先は削除対象外
     * @param bool $isSoftDelete 論理削除の場合はTrue
     */
    public function deleteExceptSupplierIdsForReserveItineraryId(int $reserveItineraryId, array $supplierIds, bool $isSoftDelete = true) : bool
    {
        return $this->accountPayableItemRepository->deleteExceptSupplierIdsForReserveItineraryId($reserveItineraryId, $supplierIds, $isSoftDelete);
    }

    /**
     * account_payable_item_idを取得
     */
    public function getIdByAccountPayableDetail(AccountPayableDetail $accountPayableDetail) : ?int
    {
        $where = [];
        foreach (config("consts.account_payable_items.ITEM_PAYABLE_NUMBER_COLUMNS") as $col) {
            $where[$col] = $accountPayableDetail->{$col};
        }

        $result = $this->accountPayableItemRepository->findWhere($where, [], ['id']);
        return $result ? $result->id : null;
    }
}
