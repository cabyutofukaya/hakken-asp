<?php

namespace App\Http\Requests\Staff;

use App\Services\CountryService;
use App\Services\UserService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ParticipantUpdateRequest extends FormRequest
{
    public function __construct(
        CountryService $countryService,
        UserService $userService
    ) {
        $this->countryService = $countryService;
        $this->userService = $userService;
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    public function validationData()
    {
        return array_merge($this->request->all(), [
            'application_step' => $this->applicationStep,
            'control_number' => $this->controlNumber,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'application_step' => 'required',
            'control_number' => 'required',
            'name' => 'nullable|max:32',
            'name_kana' => 'nullable|max:32',
            'name_roman' => 'nullable|max:100',
            'sex' => ['nullable',Rule::in(array_values(config("consts.users.SEX_LIST")))],
            'birthday_y' => ['nullable',
                function ($attribute, $value, $fail) {
                    if (!in_array($value, array_keys($this->userService->getBirthDayYearSelect()))) {
                        return $fail("生年月日(年)の指定が不正です。");
                    }
                }
            ],
            'birthday_m' => ['nullable',
                function ($attribute, $value, $fail) {
                    if (!in_array($value, array_keys($this->userService->getBirthDayMonthSelect()))) {
                        return $fail("生年月日(月)の指定が不正です。");
                    }
                }
            ],
            'birthday_d' => ['nullable',
                function ($attribute, $value, $fail) {
                    if (!in_array($value, array_keys($this->userService->getBirthDayDaySelect()))) {
                        return $fail("生年月日(日)の指定が不正です。");
                    }
                }
            ],
            'age' => 'nullable',
            'age_kbn' => ['nullable',Rule::in(array_values(config("consts.users.AGE_KBN_LIST")))],
            'mobile_phone' => 'nullable|max:100',
            'note' => 'nullable|max:1000',
            'passport_number' => 'nullable|max:100',
            'passport_issue_date' => 'nullable|date',
            'passport_expiration_date' => 'nullable|date',
            'passport_issue_country_code' => ['nullable',
                function ($attribute, $value, $fail) {
                    if (!in_array($value, array_keys($this->countryService->getCodeNameList()))) {
                        return $fail("旅券発行国の指定が不正です。");
                    }
                }
            ],
            'citizenship_code' => ['nullable',
                function ($attribute, $value, $fail) {
                    if (!in_array($value, array_keys($this->countryService->getCodeNameList()))) {
                        return $fail("国籍の指定が不正です。");
                    }
                }
            ],
            'updated_at' => 'nullable',
        ];
    }
    
    public function messages()
    {
        return [
            'application_step.required' => '申込状態は必須です。',
            'control_number.required' => '予約/見積番号は必須です。',
            'name.max' => '氏名が長すぎます(32文字まで)。',
            'name_kana.max' => '氏名(カナ)が長すぎます(32文字まで)。',
            'name_roman.max' => '氏名(ローマ字)が長すぎます(100文字まで)。',
            'sex.in' => '性別の指定が不正です。',
            'age_kbn.in' => '年齢区分の指定が不正です。',
            'mobile_phone.max' => '携帯が長すぎます(100文字まで)。',
            'note.max' => '備考が長すぎます(1000文字まで)。',
            'passport_number.max' => '旅券番号が長すぎます(100文字まで)。',
            'passport_issue_date.date' => '旅券発行日の入力形式が正しくありません(YYYY-MM-DD)。',
            'passport_expiration_date.date' => '旅券有効期限の入力形式が正しくありません(YYYY-MM-DD)。',
        ];
    }
}
