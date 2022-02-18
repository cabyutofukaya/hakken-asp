<?php
namespace App\Repositories\SupplierAccountPayable;

use App\Models\SupplierAccountPayable;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SupplierAccountPayableRepository implements SupplierAccountPayableRepositoryInterface
{
    /**
    * @param object $agency
    */
    public function __construct(SupplierAccountPayable $supplierAccountPayable)
    {
        $this->supplierAccountPayable = $supplierAccountPayable;
    }

    public function deleteBySupplierId(int $supplierId, bool $isSoftDelete) : bool
    {
        if ($isSoftDelete) {
            foreach ($this->supplierAccountPayable->where('supplier_id', $supplierId)->get() as $row) {
                $row->delete();
            }
        } else {
            foreach ($this->supplierAccountPayable->where('supplier_id', $supplierId)->get() as $row) {
                $row->forceDelete();
            }
        }
        return true;
    }
}
