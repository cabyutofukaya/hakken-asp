<?php

namespace App\Rules;

use App\Models\BusinessUser;
use Illuminate\Contracts\Validation\Rule;

class ExistBusinessUser implements Rule
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
        return BusinessUser::withTrashed()->where('agency_id', $this->agencyId)->where('id', $value)->exists(); // 論理削除も含めて取得
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '法人顧客が存在しません。';
    }
}
