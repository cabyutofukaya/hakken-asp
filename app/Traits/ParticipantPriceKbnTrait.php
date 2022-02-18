<?php

namespace App\Traits;

/**
 * 参加者の料金区分を扱うtrait
 */
trait ParticipantPriceKbnTrait
{
    // nullを0に変換するミューテータ群

    public function setGrossExAttribute($value)
    {
        $this->attributes['gross_ex'] = is_null($value) ? 0 : $value;
    }

    public function setGrossAttribute($value)
    {
        $this->attributes['gross'] = is_null($value) ? 0 : $value;
    }

    public function setCostAttribute($value)
    {
        $this->attributes['cost'] = is_null($value) ? 0 : $value;
    }

    public function setCommissionRateAttribute($value)
    {
        $this->attributes['commission_rate'] = is_null($value) ? 0 : $value;
    }

    public function setNetAttribute($value)
    {
        $this->attributes['net'] = is_null($value) ? 0 : $value;
    }

    public function setGrossProfitAttribute($value)
    {
        $this->attributes['gross_profit'] = is_null($value) ? 0 : $value;
    }
}
