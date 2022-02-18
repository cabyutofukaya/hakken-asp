<?php

namespace App\Rules;

use App\Models\User;
use Illuminate\Contracts\Validation\Rule;

class ExistUserNumber implements Rule
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
        return User::withTrashed()->where('agency_id', $this->agencyId)->where('user_number', $value)->exists(); // 論理削除も含めてチェック
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '個人顧客が存在しません。';
    }
}
