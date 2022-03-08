<?php

namespace App\Services;

use App\Exceptions\ExclusiveLockException;
use App\Models\AccountPayableDetail;
use App\Models\Supplier;
use App\Repositories\AccountPayableDetail\AccountPayableDetailRepository;
use App\Repositories\Agency\AgencyRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class AccountPayableDetailService
{
    public function __construct(AgencyRepository $agencyRepository, AccountPayableDetailRepository $accountPayableDetailRepository)
    {
        $this->agencyRepository = $agencyRepository;
        $this->accountPayableDetailRepository = $accountPayableDetailRepository;
    }

    /**
     * 該当IDを一件取得
     *
     * @param int $id ID
     * @param array $select 取得カラム
     * @param bool $isLock 行ロックして取得する場合はtrue
     */
    public function find(int $id, array $with = [], array $select=[], bool $isLock = false) : ?AccountPayableDetail
    {
        return $this->accountPayableDetailRepository->find($id, $with, $select, $isLock);
    }

    /**
     * 検索して一件取得
     */
    public function findWhere(array $where, array $with=[], array $select=[]): ?AccountPayableDetail
    {
        return $this->accountPayableDetailRepository->findWhere($where, $with, $select);
    }

    /**
     * 一覧を取得
     *
     * @param string $agencyAccount
     * @param int $applicationStep 予約段階（見積/予約）
     * @param int $limit
     * @param array $with
     * @param bool $isValid 有効にチェックが入っている仕入のみ対象の場合はtrue
     * @param
     */
    public function paginateByAgencyAccount(string $agencyAccount, array $params, int $limit, bool $isValid = true, ?string $applicationStep = null, array $with = [], array $select=[]) : LengthAwarePaginator
    {
        $agencyId = $this->agencyRepository->getIdByAccount($agencyAccount);
        return $this->accountPayableDetailRepository->paginateByAgencyId($agencyId, $params, $limit, $isValid, $applicationStep, $with, $select);
    }

    /**
     * 更新
     *
     * @throws ExclusiveLockException 同時編集を検知した場合は例外を投げる
     */
    public function update(int $id, array $data): AccountPayableDetail
    {
        $accountPayableDetail = $this->accountPayableDetailRepository->find($id);
        if ($accountPayableDetail->updated_at != Arr::get($data, 'updated_at')) {
            throw new ExclusiveLockException;
        }

        return $this->accountPayableDetailRepository->update($id, $data);
    }

    /**
     * 未払い金額とステータスを更新
     */
    public function updateStatusAndUnpaidBalance($id, int $unpaidBalance, $status)
    {
        return $this->accountPayableDetailRepository->updateField($id, ['unpaid_balance' => $unpaidBalance, 'status' => $status]);
    }

    /**
     * 登録or更新
     *
     */
    public function updateOrCreate(array $where, array $params) : ?AccountPayableDetail
    {
        return $this->accountPayableDetailRepository->updateOrCreate($where, $params);
    }

    /**
     * 任意のフィールドを更新
     *
     * @param int $id account_payable_details ID
     */
    public function updateFields(int $id, $params)
    {
        return $this->accountPayableDetailRepository->updateField($id, $params);
    }

    /**
     * 仕入先へのキャンセルチャージ料金を設定
     *
     * @param int $cancelCharge キャンセルチャージ金額
     * @param string $saleableType 販売種別
     * @param int $saleableId 販売ID
     * @param bool $getUpdatedId 更新対象のレコードIDがを取得する場合はtrue
     */
    public function setCancelChargeBySaleableId(int $cancelCharge, string $saleableType, int $saleableId, bool $getUpdatedId = false) : ?int
    {
        $updatedId = null;
        if ($getUpdatedId) {
            $res = $this->accountPayableDetailRepository->findWhere(['saleable_type' => $saleableType, 'saleable_id' => $saleableId], [], ['id']);
            $updatedId = $res->id;
        }
        $this->accountPayableDetailRepository->updateWhere(
            ['amount_billed' => $cancelCharge],
            ['saleable_type' => $saleableType, 'saleable_id' => $saleableId]
        );

        return $updatedId;
    }

    /**
     * 対象予約IDの仕入情報を取得
     */
    public function getByReserveId(int $reserveId, array $with = [], array $select = []) : Collection
    {
        return $this->accountPayableDetailRepository->getWhere(['reserve_id' => $reserveId], $with, $select);
    }
    
    /**
     * 削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
     */
    public function delete(int $id, bool $isSoftDelete=true): bool
    {
        return $this->accountPayableDetailRepository->delete($id, $isSoftDelete);
    }
}
