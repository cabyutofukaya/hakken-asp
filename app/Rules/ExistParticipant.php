<?php

namespace App\Rules;

use App\Models\Participant;
use Illuminate\Contracts\Validation\Rule;

class ExistParticipant implements Rule
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
        return Participant::withTrashed()->where('agency_id', $this->agencyId)->where('id', $value)->exists(); // 論理削除も含めてチェック
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '参加者情報が存在しません。';
    }
}
