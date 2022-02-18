<?php
namespace App\Repositories\ReservePurchasingSubjectHotelCustomValue;

use App\Models\ReservePurchasingSubjectHotelCustomValue;
use Illuminate\Support\Collection;
use DB;
use Illuminate\Database\Eloquent\Model;

class ReservePurchasingSubjectHotelCustomValueRepository implements ReservePurchasingSubjectHotelCustomValueRepositoryInterface
{
    /**
    * @param object $userCustomItem
    */
    public function __construct(ReservePurchasingSubjectHotelCustomValue $reservePurchasingSubjectHotelCustomValue)
    {
        $this->reservePurchasingSubjectHotelCustomValue = $reservePurchasingSubjectHotelCustomValue;
    }

    /**
     * update or insert
     *
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function updateOrCreate(array $attributes, array $values = []) : Model
    {
        return $this->reservePurchasingSubjectHotelCustomValue->updateOrCreate(
            $attributes,
            $values
        );
    }

}
