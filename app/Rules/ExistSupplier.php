<?php

namespace App\Rules;

use App\Models\Supplier;
use Illuminate\Contracts\Validation\Rule;

/**
 * 仕入先が存在するかチェック
 */
class ExistSupplier implements Rule
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
        return Supplier::withTrashed()->where('agency_id', $this->agencyId)->where('id', $value)->exists();// 論理削除も含めてチェック
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '選択された仕入先データは存在しません。';
    }
}
