<?php

namespace App\Rules;

use App\Models\Reserve;
use Illuminate\Contracts\Validation\Rule;

/**
 * 旅行期間をチェックして、出金登録されている日付があればエラーを出すバリデーション
 */
class CheckTravelPeriod implements Rule
{
    protected $agencyId;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($agencyId)
    {
        $this->agencyId = $agencyId;
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
        $reserve = Reserve::where('agency_id', $this->agencyId)->where('control_number', $value)->first();
        return preg_match("/^[a-zA-Z0-9\-_\.\!'\(\)]+$/", $value);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '商品コードは半角英数、「-(ハイフン)」「_(アンダーバー)」が利用できます。';
    }
}
