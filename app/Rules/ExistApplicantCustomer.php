<?php

namespace App\Rules;

use App\Models\User;
use App\Models\BusinessUserManager;
use Illuminate\Contracts\Validation\Rule;

/**
 * POSTされた顧客情報（申込用）が存在するかチェック
 * 論理削除含む
 */
class ExistApplicantCustomer implements Rule
{
    protected $agencyId;
    protected $customerType;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($agencyId, $customerType)
    {
        $this->agencyId = $agencyId;
        $this->customerType = $customerType;
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
        if ($this->customerType === config('consts.reserves.PARTICIPANT_TYPE_PERSON')) { // 個人顧客
            return User::withTrashed()->where('agency_id', $this->agencyId)->where('user_number', $value)->exists();
        } elseif ($this->customerType === config('consts.reserves.PARTICIPANT_TYPE_BUSINESS')) { // 法人顧客
            return BusinessUserManager::withTrashed()->where('agency_id', $this->agencyId)->where('user_number', $value)->exists();
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '選択された顧客データは存在しません。';
    }
}
