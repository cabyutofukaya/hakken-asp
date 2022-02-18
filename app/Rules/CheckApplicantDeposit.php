<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * 申込者の入金登録をチェック
 */
class CheckApplicantDeposit implements Rule
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $reserve = $this->reserveService->findByControlNumber($this->reserveNumber, auth('staff')->user()->agency->account);

        // 入金登録がなければ申込者の変更チェックは不要（sum_depositは当該予約の入金合計）
        if ($reserve->sum_deposit === 0) {
            return;
        }

        /**
         * 入金登録がある場合は申込者情報が変更されているかチェック
         *
         * 申込者情報が変更されている場合は更新不可。
         * ただし、同一法人で担当者が変わるケースは入金レコードが存在していても変更可
         */
        $applicantInfo = $this->getApplicantCustomerIdInfo($this->agencyAccount, $this->participant_type, $value, $this->userService, $this->businessUserManagerService);

        if ($reserve->applicantable_type !== $applicantInfo['applicantable_type'] || $reserve->applicantable_id !== $applicantInfo['applicantable_id']) { // 申込者情報が変更

            // 変更後の申込者情報
            if ($applicantInfo['applicantable_type'] === 'App\Models\BusinessUserManager') { // 法人顧客ユーザー
                $newApplicant = $this->businessUserManagerService->find($applicantInfo['applicantable_id'], [], [], true); // 一応、削除済みも取得
            } elseif ($applicantInfo['applicantable_type'] === 'App\Models\User') { // 個人ユーザー
                $newApplicant = $this->userService->find($applicantInfo['applicantable_id'], [], [], true); // 一応、削除済みも取得
            }

            if ($reserve->applicantable->applicant_type === config('consts.reserves.PARTICIPANT_TYPE_BUSINESS') && $newApplicant->applicant_type === config('consts.reserves.PARTICIPANT_TYPE_BUSINESS') && $reserve->applicantable->business_user_id === $newApplicant->business_user_id) {
                // 同じ会社で担当者が変わるケース。この場合は入金レコードがあっても問題ナシ
            } else {
                // 上記以外。入金登録があり、且つ申込者情報が変更されているケース
                return false;
            }
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '入金登録があるため申込者情報を変更できません。';
    }
}
