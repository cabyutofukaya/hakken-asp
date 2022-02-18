<?php

namespace App\Rules;

use App\Models\VArea;
use Illuminate\Contracts\Validation\Rule;

/**
 * POSTされたUUIDがv_areasレコードに存在するかチェックするバリデーション
 */
class ExistArea implements Rule
{
    protected $agencyId;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct(int $agencyId)
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
        // uuidが存在するかチェック。スーパーマスターが提供するレコードも検索対象
        return VArea::where(function ($q) {
            $q->where('agency_id', $this->agencyId)
                ->orWhere('agency_id', config('consts.const.MASTER_AGENCY_ID'));
        })->where('uuid', $value)->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return ':attributeが存在しない国・地域です。';
    }
}
