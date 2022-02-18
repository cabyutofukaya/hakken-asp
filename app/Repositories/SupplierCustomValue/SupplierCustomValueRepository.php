<?php
namespace App\Repositories\SupplierCustomValue;

use App\Models\SupplierCustomValue;
use Illuminate\Support\Collection;
use DB;
use Illuminate\Database\Eloquent\Model;

class SupplierCustomValueRepository implements SupplierCustomValueRepositoryInterface
{
    /**
    * @param object $userCustomItem
    */
    public function __construct(SupplierCustomValue $supplierCustomValue)
    {
        $this->supplierCustomValue = $supplierCustomValue;
    }

    /**
     * update or insert
     *
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function updateOrCreate(array $attributes, array $values = []) : Model
    {
        return $this->supplierCustomValue->updateOrCreate(
            $attributes,
            $values
        );
    }

}
