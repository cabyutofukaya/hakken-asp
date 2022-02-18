<?php
namespace App\Repositories\ReservePurchasingSubjectAirplaneCustomValue;

use App\Models\ReservePurchasingSubjectAirplaneCustomValue;
use Illuminate\Support\Collection;
use DB;
use Illuminate\Database\Eloquent\Model;

class ReservePurchasingSubjectAirplaneCustomValueRepository implements ReservePurchasingSubjectAirplaneCustomValueRepositoryInterface
{
    /**
    * @param object $userCustomItem
    */
    public function __construct(ReservePurchasingSubjectAirplaneCustomValue $reservePurchasingSubjectAirplaneCustomValue)
    {
        $this->reservePurchasingSubjectAirplaneCustomValue = $reservePurchasingSubjectAirplaneCustomValue;
    }

    /**
     * update or insert
     *
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function updateOrCreate(array $attributes, array $values = []) : Model
    {
        return $this->reservePurchasingSubjectAirplaneCustomValue->updateOrCreate(
            $attributes,
            $values
        );
    }

}
