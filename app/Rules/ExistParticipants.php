<?php

namespace App\Rules;

use App\Models\Participant;
use Illuminate\Contracts\Validation\Rule;

// ExistParticipantの複数ID対応版
class ExistParticipants implements Rule
{
    protected $agencyId;
    protected $participantIds;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($agencyId, array $participantIds)
    {
        $this->agencyId = $agencyId;
        $this->participantIds = $participantIds;
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
        return Participant::withTrashed()->where('agency_id', $this->agencyId)->whereIn('id', $this->participantIds)->count() === count($this->participantIds); // 参加者IDリストとsqlの件数が同じであること。論理削除も含めてチェック
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '存在しない参加者IDが含まれています。';
    }
}
