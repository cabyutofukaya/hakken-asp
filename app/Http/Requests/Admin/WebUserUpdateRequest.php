<?php

namespace App\Http\Requests\Admin;

use App\Services\CountryService;
use App\Services\PrefectureService;
use App\Services\UserService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WebUserUpdateRequest extends FormRequest
{
    public function __construct(
        CountryService $countryService, 
        PrefectureService $prefectureService, 
        UserService $userService
        )
    {
        $this->countryService = $countryService;
        $this->prefectureService = $prefectureService;
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'nullable|max:32',
            'name_kana' => 'nullable|max:32',
            'name_roman' => 'nullable|max:100',
            'sex' => ['nullable',Rule::in(array_values(config("consts.users.SEX_LIST")))],
            'birthday_y' => ['nullable',
                function ($attribute, $value, $fail) {
                    if(!in_array($value, array_keys($this->userService->getBirthDayYearSelect()))){
                        return $fail("生年月日(年)の指定が不正です。");
                    }
                }
            ],
            'birthday_m' => ['nullable',
                function ($attribute, $value, $fail) {
                    if(!in_array($value, array_keys($this->userService->getBirthDayMonthSelect()))){
                        return $fail("生年月日(月)の指定が不正です。");
                    }
                }
            ],
            'birthday_d' => ['nullable',
                function ($attribute, $value, $fail) {
                    if(!in_array($value, array_keys($this->userService->getBirthDayDaySelect()))){
                        return $fail("生年月日(日)の指定が不正です。");
                    }
                }
            ],
            'age_kbn' => ['nullable',Rule::in(array_values(config("consts.users.AGE_KBN_LIST")))],
            'mobile_phone' => 'nullable|max:100',
            'tel' => 'nullable|max:100',
            'fax' => 'nullable|max:100',
            'zip_code' => 'nullable|max:32',
            'prefecture_code' => ['nullable',
                function ($attribute, $value, $fail) {
                    if(!in_array($value, array_keys($this->prefectureService->getCodeNameList()))){
                        return $fail("都道府県の指定が不正です。");
                    }
                }
            ],
            'address1' => 'nullable|max:100',
            'address2' => 'nullable|max:100',
            'passport_number' => 'nullable|max:100',
            'passport_issue_date' => 'nullable|date',
            'passport_expiration_date' => 'nullable|date',
            'passport_issue_country_code' => ['nullable',
                function ($attribute, $value, $fail) {
                    if(!in_array($value, array_keys($this->countryService->getCodeNameList()))){
                        return $fail("旅券発行国の指定が不正です。");
                    }
                }
            ],
            'citizenship_code' => ['nullable',
                function ($attribute, $value, $fail) {
                    if(!in_array($value, array_keys($this->countryService->getCodeNameList()))){
                        return $fail("国籍の指定が不正です。");
                    }
                }
            ],
            'workspace_name' => 'nullable|max:100',
            'workspace_address' => 'nullable|max:200',
            'workspace_tel' => 'nullable|max:100',
            'workspace_note' => 'nullable|max:1000',
        ];
    }
    
    public function messages()
    {
        return [
            'name.max' => '氏名が長すぎます(32文字まで)。',
            'name_kana.max' => '氏名(カナ)が長すぎます(32文字まで)。',
            'name_roman.max' => '氏名(ローマ字)が長すぎます(100文字まで)。',
            'sex.in' => '性別の指定が不正です。',
            'age_kbn.in' => '年齢区分の指定が不正です。',
            'mobile_phone.max' => '携帯が長すぎます(100文字まで)。',
            'tel.max' => '固定電話が長すぎます(100文字まで)。',
            'fax.max' => 'FAXが長すぎます(100文字まで)。',
            'zip_code.max' => '郵便番号が長すぎます(32文字まで)。',
            'address1.max' => '住所が長すぎます(100文字まで)。',
            'address2.max' => 'ビル・建物名が長すぎます(100文字まで)。',
            'passport_number.max' => '旅券番号が長すぎます(100文字まで)。',
            'passport_issue_date.date' => '旅券発行日の入力形式が正しくありません(YYYY-MM-DD)。',
            'passport_expiration_date.date' => '旅券有効期限の入力形式が正しくありません(YYYY-MM-DD)。',
            'workspace_name.max' => '名称(勤務先/学校)が長すぎます(100文字まで)。',
            'workspace_address.max' => '住所(勤務先/学校)が長すぎます(200文字まで)。',
            'workspace_tel.max' => '電話番号(勤務先/学校)が長すぎます(100文字まで)。',
            'workspace_note.max' => '備考(勤務先/学校)が長すぎます(1000文字まで)。',
        ];
    }
}
