<?php

namespace App\Traits;

/**
 * 料金区分を扱うtrait
 */
trait ChargeKbnTrait
{
    // nullを0に変換するミューテータ群

    public function setAdGrossExAttribute($value)
    {
        $this->attributes['ad_gross_ex'] = is_null($value) ? 0 : $value;
    }

    public function setAdGrossAttribute($value)
    {
        $this->attributes['ad_gross'] = is_null($value) ? 0 : $value;
    }

    public function setAdCostAttribute($value)
    {
        $this->attributes['ad_cost'] = is_null($value) ? 0 : $value;
    }

    public function setAdCommissionRateAttribute($value)
    {
        $this->attributes['ad_commission_rate'] = is_null($value) ? 0 : $value;
    }

    public function setAdNetAttribute($value)
    {
        $this->attributes['ad_net'] = is_null($value) ? 0 : $value;
    }

    public function setAdGrossProfitAttribute($value)
    {
        $this->attributes['ad_gross_profit'] = is_null($value) ? 0 : $value;
    }

    public function setChGrossExAttribute($value)
    {
        $this->attributes['ch_gross_ex'] = is_null($value) ? 0 : $value;
    }

    public function setChGrossAttribute($value)
    {
        $this->attributes['ch_gross'] = is_null($value) ? 0 : $value;
    }

    public function setChCostAttribute($value)
    {
        $this->attributes['ch_cost'] = is_null($value) ? 0 : $value;
    }

    public function setChCommissionRateAttribute($value)
    {
        $this->attributes['ch_commission_rate'] = is_null($value) ? 0 : $value;
    }

    public function setChNetAttribute($value)
    {
        $this->attributes['ch_net'] = is_null($value) ? 0 : $value;
    }

    public function setChGrossProfitAttribute($value)
    {
        $this->attributes['ch_gross_profit'] = is_null($value) ? 0 : $value;
    }

    public function setInfGrossExAttribute($value)
    {
        $this->attributes['inf_gross_ex'] = is_null($value) ? 0 : $value;
    }

    public function setInfGrossAttribute($value)
    {
        $this->attributes['inf_gross'] = is_null($value) ? 0 : $value;
    }

    public function setInfCostAttribute($value)
    {
        $this->attributes['inf_cost'] = is_null($value) ? 0 : $value;
    }

    public function setInfCommissionRateAttribute($value)
    {
        $this->attributes['inf_commission_rate'] = is_null($value) ? 0 : $value;
    }

    public function setInfNetAttribute($value)
    {
        $this->attributes['inf_net'] = is_null($value) ? 0 : $value;
    }

    public function setInfGrossProfitAttribute($value)
    {
        $this->attributes['inf_gross_profit'] = is_null($value) ? 0 : $value;
    }
}
