<?php

namespace App\Services;

use App\Models\SupplierCustomValue;
use Illuminate\Support\Arr;
use App\Repositories\SupplierCustomValue\SupplierCustomValueRepository;
use App\Repositories\UserCustomItem\UserCustomItemRepository;

class SupplierCustomValueService
{
    public function __construct(SupplierCustomValueRepository $supplierCustomValueRepository, UserCustomItemRepository $userCustomItemRepository)
    {
        $this->supplierCustomValueRepository = $supplierCustomValueRepository;
        $this->userCustomItemRepository = $userCustomItemRepository;
    }

    /**
     * カスタム項目値をinsert or update
     *
     * @param array $fields 「項目キー => 値」形式の配列
     * @param int $supplierId 仕入先ID
     * @return bool
     */
    public function upsertCustomFileds(array $fields, int $supplierId) : bool
    {
        $userCustomItems = $this->userCustomItemRepository->getByKeys(array_keys($fields,), [], ['id','key']);

        foreach ($userCustomItems as $uci) {
            $this->supplierCustomValueRepository->updateOrCreate(
                ['supplier_id' => $supplierId, 'user_custom_item_id' => $uci->id],
                ['val' => Arr::get($fields, $uci->key)]
            );
        }

        return true;
    }

    /**
     * 当該仕入IDに紐づくカスタム項目値を削除
     *
     * @param int $supplierId 仕入先ID
     * @param bool $isSoftDelete 論理削除の場合はtrue
     * @return bool
     */
    public function deleteBySupplierId(int $supplierId, bool $isSoftDelete=true) : bool
    {
        return $this->supplierCustomValueRepository->deleteBySupplierId($supplierId, $isSoftDelete);
    }
}
