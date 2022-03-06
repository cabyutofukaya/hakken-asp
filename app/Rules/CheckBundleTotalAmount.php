<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * 一括請求の合計金額をチェックするバリデーション
 *
 */
class CheckBundleTotalAmount implements Rule
{
    protected $partnerManagerIds;
    protected $reservePrices;
    protected $reserveCancelInfo;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(array $partnerManagerIds, array $reservePrices, array $reserveCancelInfo)
    {
        $this->partnerManagerIds = $partnerManagerIds;
        $this->reservePrices = $reservePrices;
        $this->reserveCancelInfo = $reserveCancelInfo;
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
        return get_reserve_price_total($this->partnerManagerIds, $this->reservePrices, $this->reserveCancelInfo) === (int)$value;
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
