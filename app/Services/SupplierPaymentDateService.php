<?php

namespace App\Services;

use App\Repositories\SupplierPaymentDate\SupplierPaymentDateRepository;

class SupplierPaymentDateService
{
    public function __construct(SupplierPaymentDateRepository $supplierPaymentDateRepository)
    {
        $this->supplierPaymentDateRepository = $supplierPaymentDateRepository;
    }

    /**
     * 当該予約に関する仕入先の支払日情報をセット
     *
     * @param array 仕入先IDと支払日情報の配列
     */
    public function setPaymentDatesByReserveId(int $reserveId, array $data) : bool
    {
        $insertRows = []; // insertデータ
        $updateRows = []; // updateデータ

        foreach ($data as $supplierId => $paymentDate) {
            if (($r = $this->supplierPaymentDateRepository->findWhere(['reserve_id' => $reserveId, 'supplier_id' => $supplierId]))) { // 更新
                $row = [];
                $row['id'] = $r->id;
                $row['payment_date'] = $paymentDate;
                $updateRows[] = $row;
            } else { // 新規
                $row = [];
                $row['reserve_id'] = $reserveId;
                $row['supplier_id'] = $supplierId;
                $row['payment_date'] = $paymentDate;
                $insertRows[] = $row;
            }
        }

        if ($insertRows) {
            $this->supplierPaymentDateRepository->insert($insertRows); // バルクインサート
        }

        if ($updateRows) {
            $this->supplierPaymentDateRepository->updateBulk($updateRows, 'id'); // バルクupdate
        }

        return true;
    }
}
