<?php

namespace App\Rules;

use Hashids;
use App\Models\Staff;
use Illuminate\Contracts\Validation\Rule;

class ExistStaff implements Rule
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
        return Staff::withTrashed()->where('agency_id', $this->agencyId)->where('id', $value)->exists(); // 論理削除も含めて取得
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '自社担当が存在しません。';
    }
}
