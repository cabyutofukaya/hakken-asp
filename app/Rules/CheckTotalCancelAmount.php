<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * 行程のキャンセル金額をチェックするバリデーション
 *
 */
class CheckTotalCancelAmount implements Rule
{
    protected $participantIds;
    protected $optionPrices;
    protected $airticketPrices;
    protected $hotelPrices;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($participantIds, $optionPrices, $airticketPrices, $hotelPrices)
    {
        $this->participantIds = $participantIds;
        $this->optionPrices = $optionPrices;
        $this->airticketPrices = $airticketPrices;
        $this->hotelPrices = $hotelPrices;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        return get_cancel_charge_total($this->participantIds ?? [], $this->optionPrices, $this->airticketPrices, $this->hotelPrices) === (int)$value;
    }


    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '代金内訳合計金額が正しくありません。';
    }
}
