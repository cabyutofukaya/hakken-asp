<?php

namespace App\Services;

use Hashids;
use App\Models\Supplier;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\Supplier\SupplierRepository;
use App\Repositories\SupplierAccountPayable\SupplierAccountPayableRepository;
use App\Services\SupplierCustomValueService;
use App\Traits\ConstsTrait;
use App\Traits\UserCustomItemTrait;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class SupplierService
{
    use ConstsTrait, UserCustomItemTrait;
    
    public function __construct(
        AgencyRepository $agencyRepository, 
        SupplierCustomValueService $supplierCustomValueService,
        SupplierRepository $supplierRepository, SupplierAccountPayableRepository $supplierAccountPayableRepository
        )
    {
        $this->agencyRepository = $agencyRepository;
        $this->supplierAccountPayableRepository = $supplierAccountPayableRepository;
        $this->supplierCustomValueService = $supplierCustomValueService;
        $this->supplierRepository = $supplierRepository;
    }

    /**
     * 該当IDを一件取得
     *
     * @param int $id ID
     * @param array $select 取得カラム
     */
    public function find(int $id, array $select=[], bool $getDeleted=false) : ?Supplier
    {
        return $this->supplierRepository->find($id, $select, $getDeleted);
    }

    /**
     * 一覧を取得（アカウント用）
     *
     * @param string $account
     * @param int $limit
     * @param array $with
     */
    public function paginateByAgencyAccount(string $account, array $params, int $limit, array $with = [], array $select=[]) : LengthAwarePaginator
    {
        $agencyId = $this->agencyRepository->getIdByAccount($account);
        return $this->supplierRepository->paginateByAgencyId($agencyId, $params, $limit, $with, $select);
    }

    public function create(array $data): Supplier
    {
        $supplier = $this->supplierRepository->create($data);
        
        $customFields = $this->customFieldsExtraction($data); // 入力データからカスタムフィールドを抽出
        if ($customFields) {
            $this->supplierCustomValueService->upsertCustomFileds($customFields, $supplier->id); // カスタムフィールド保存
        }

        foreach (Arr::get($data, 'supplier_account_payables') as $row) { // 振込先情報を保存
            $supplier->supplier_account_payables()->create($row);
        }
        
        return $supplier;
    }

    public function update(int $id, array $data): Supplier
    {
        // コードは更新不可なので一応、配列に入っていたらカットしておく
        if (isset($data['code'])) {
            unset($data['code']);
        }
        $supplier = $this->supplierRepository->update($id, $data);

        $customFields = $this->customFieldsExtraction($data); // 入力データからカスタムフィールドを抽出
        if ($customFields) {
            $this->supplierCustomValueService->upsertCustomFileds($customFields, $supplier->id); // カスタムフィールド保存
        }

        /**
         * 振込先情報の登録
         * 既存データを一旦削除 -> 新規登録
         */
        $this->supplierAccountPayableRepository->deleteBySupplierId($supplier->id, false);
        foreach (Arr::get($data, 'supplier_account_payables') as $sap) { // 振込先情報を保存
            $supplier->supplier_account_payables()->create($sap);
        }

        return $supplier;
    }

    /**
     * 削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
     */
    public function delete(int $id, bool $isSoftDelete=true): bool
    {
        return $this->supplierRepository->delete($id, $isSoftDelete);
    }

    /**
     * 1日〜月末までの日付リストを取得（セレクトメニュー用）
     */
    public function getDateSelect() : array
    {
        $vals = array_map(function ($n) {
            return "{$n}日";
        }, range(1, 31));
        $vals[] = "月末";

        return array_combine(
            range(1, config('consts.const.END_OF_MONTH')),
            $vals
        );
    }

    /**
     * selectメニュー用の名前配列
     * 
     * @param string $account 会社アカウント
     * @param bool $getDeleted 削除済みも取得する場合はtrue
     * @return array
     */
    public function getNameSelectByAgencyAccount(string $account, bool $getDeleted = false) : array
    {
        $agencyId = $this->agencyRepository->getIdByAccount($account);
        return $this->supplierRepository->allByAgencyId($agencyId, [], ['id','name','code','deleted_at'],'id','asc', $getDeleted)->map(function($supplier, $key){
            return [
                'id' => $supplier->id, // IDをハッシュ化
                'name' => sprintf("%s %s", $supplier->code, $supplier->name)
            ];
        })->pluck('name', 'id')->toArray();
    }
}
