<?php
namespace App\Repositories\SupplierPaymentDate;

use App\Models\SupplierPaymentDate;

class SupplierPaymentDateRepository
{
    /**
    * @param object $supplierPaymentDate
    */
    public function __construct(SupplierPaymentDate $supplierPaymentDate)
    {
        $this->supplierPaymentDate = $supplierPaymentDate;
    }

    /**
     * 検索して1件取得
     */
    public function findWhere(array $where, array $with=[], array $select=[]) : ?SupplierPaymentDate
    {
        $query = $this->supplierPaymentDate;
        
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;

        foreach ($where as $key => $val) {
            if (is_empty($val)) {
                continue;
            }
            $query = $query->where($key, $val);
        }
        return $query->first();
    }

    /**
     * バルクインサート
     */
    public function insert(array $rows) : bool
    {
        $this->supplierPaymentDate->insert($rows);
        return true;
    }

    /**
     * バルクアップデート
     *
     * @param array $params
     */
    public function updateBulk(array $params, string $id) : bool
    {
        $this->supplierPaymentDate->updateBulk($params, $id);
        return true;
    }
}
