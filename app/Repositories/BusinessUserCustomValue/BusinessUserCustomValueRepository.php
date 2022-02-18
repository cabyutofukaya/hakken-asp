<?php
namespace App\Repositories\BusinessUserCustomValue;

use App\Models\BusinessUserCustomValue;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class BusinessUserCustomValueRepository implements BusinessUserCustomValueRepositoryInterface
{
    /**
    * @param object $userCustomItem
    */
    public function __construct(BusinessUserCustomValue $businessUserCustomValue)
    {
        $this->businessUserCustomValue = $businessUserCustomValue;
    }

    /**
     * update or insert
     *
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function updateOrCreate(array $attributes, array $values = []) : Model
    {
        return $this->businessUserCustomValue->updateOrCreate(
            $attributes,
            $values
        );
    }

}
