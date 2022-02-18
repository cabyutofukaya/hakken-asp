<?php
namespace App\Repositories\ReservePurchasingSubjectOptionCustomValue;

use App\Models\ReservePurchasingSubjectOptionCustomValue;
use Illuminate\Support\Collection;
use DB;
use Illuminate\Database\Eloquent\Model;

class ReservePurchasingSubjectOptionCustomValueRepository implements ReservePurchasingSubjectOptionCustomValueRepositoryInterface
{
    /**
    * @param object $userCustomItem
    */
    public function __construct(ReservePurchasingSubjectOptionCustomValue $reservePurchasingSubjectOptionCustomValue)
    {
        $this->reservePurchasingSubjectOptionCustomValue = $reservePurchasingSubjectOptionCustomValue;
    }

    /**
     * update or insert
     *
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function updateOrCreate(array $attributes, array $values = []) : Model
    {
        return $this->reservePurchasingSubjectOptionCustomValue->updateOrCreate(
            $attributes,
            $values
        );
    }

}
