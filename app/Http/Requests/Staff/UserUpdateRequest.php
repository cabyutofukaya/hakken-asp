<?php

namespace App\Http\Requests\Staff;

use App\Rules\ExistStaff;
use App\Services\CountryService;
use App\Services\PrefectureService;
use App\Services\UserService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
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

    public function validationData()
    {
        return array_merge($this->request->all(), [
            'user_number' => $this->userNumber,
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
            'user_number' => 'required',
            'userable.name' => 'nullable|max:32',
            'userable.name_kana' => 'nullable|max:32',
            'userable.name_roman' => 'nullable|max:100',
            'userable.sex' => ['nullable',Rule::in(array_values(config("consts.users.SEX_LIST")))],
            'userable.birthday_y' => ['nullable',
                function ($attribute, $value, $fail) {
                    if(!in_array($value, array_keys($this->userService->getBirthDayYearSelect()))){
                        return $fail("生年月日(年)の指定が不正です。");
                    }
                }
            ],
            'userable.birthday_m' => ['nullable',
                function ($attribute, $value, $fail) {
                    if(!in_array($value, array_keys($this->userService->getBirthDayMonthSelect()))){
                        return $fail("生年月日(月)の指定が不正です。");
                    }
                }
            ],
            'userable.birthday_d' => ['nullable',
                function ($attribute, $value, $fail) {
                    if(!in_array($value, array_keys($this->userService->getBirthDayDaySelect()))){
                        return $fail("生年月日(日)の指定が不正です。");
                    }
                }
            ],
            'userable.user_ext.age_kbn' => ['nullable',Rule::in(array_values(config("consts.users.AGE_KBN_LIST")))],
            'userable.mobile_phone' => 'nullable|max:100',
            'userable.tel' => 'nullable|max:100',
            'userable.fax' => 'nullable|max:100',
            'userable.email' => 'nullable|max:255',
            'userable.user_ext.emergency_contact_column' => 'nullable|max:100',
            'userable.zip_code' => 'nullable|max:32',
            'userable.prefecture_code' => ['nullable',
                function ($attribute, $value, $fail) {
                    if(!in_array($value, array_keys($this->prefectureService->getCodeNameList()))){
                        return $fail("都道府県の指定が不正です。");
                    }
                }
            ],
            'userable.address1' => 'nullable|max:100',
            'userable.address2' => 'nullable|max:100',
            'userable.passport_number' => 'nullable|max:100',
            'userable.passport_issue_date' => 'nullable|date',
            'userable.passport_expiration_date' => 'nullable|date',
            'userable.passport_issue_country_code' => ['nullable',
                function ($attribute, $value, $fail) {
                    if(!in_array($value, array_keys($this->countryService->getCodeNameList()))){
                        return $fail("旅券発行国の指定が不正です。");
                    }
                }
            ],
            'userable.citizenship_code' => ['nullable',
                function ($attribute, $value, $fail) {
                    if(!in_array($value, array_keys($this->countryService->getCodeNameList()))){
                        return $fail("国籍の指定が不正です。");
                    }
                }
            ],
            'userable.workspace_name' => 'nullable|max:100',
            'userable.workspace_address' => 'nullable|max:200',
            'userable.workspace_tel' => 'nullable|max:100',
            'userable.workspace_note' => 'nullable|max:1000',
            'userable.user_ext.manager_id' => ['nullable',new ExistStaff(auth('staff')->user()->agency->id)],
            'userable.user_ext.dm' => ['nullable',Rule::in(array_values(config("consts.users.DM_LIST")))],
            'userable.user_ext.age' => 'nullable',
            'userable.user_ext.note' => 'nullable|max:1000',
            'user_visas' => 'nullable|array',
            'user_visas.*.number' => 'nullable|max:100',
            'user_visas.*.country_code' => ['nullable',
                function ($attribute, $value, $fail) {
                    if(!in_array($value, array_keys($this->countryService->getCodeNameList()))){
                        return $fail("国の指定が不正です。");
                    }
                }
            ],
            'user_visas.*.kind' => 'nullable|max:100',
            'user_visas.*.issue_place_code' => ['nullable',
                function ($attribute, $value, $fail) {
                    if(!in_array($value, array_keys($this->countryService->getCodeNameList()))){
                        return $fail("発行地の指定が不正です。");
                    }
                }
            ],
            'user_visas.*.issue_date' => 'nullable|date',
            'user_visas.*.expiration_date' => 'nullable|date',
            'user_visas.*.note' => 'nullable|max:1000',
            'user_mileages' => 'nullable|array',
            'user_mileages.*.airline' => 'nullable|max:100',
            'user_mileages.*.card_number' => 'nullable|max:100',
            'user_mileages.*.note' => 'nullable|max:1000',
            'user_member_cards' => 'nullable|array',
            'user_member_cards.*.card_name' => 'nullable|max:100',
            'user_member_cards.*.card_number' => 'nullable|max:100',
            'user_member_cards.*.note' => 'nullable|max:1000',
        ];
    }
    
    public function messages()
    {
        return [
            'user_number.required' => '顧客番号は必須です。',
            'userable.name.max' => '氏名が長すぎます(32文字まで)。',
            'userable.name_kana.max' => '氏名(カナ)が長すぎます(32文字まで)。',
            'userable.name_roman.max' => '氏名(ローマ字)が長すぎます(100文字まで)。',
            'userable.sex.in' => '性別の指定が不正です。',
            'userable.user_ext.age_kbn.in' => '年齢区分の指定が不正です。',
            'userable.mobile_phone.max' => '携帯が長すぎます(100文字まで)。',
            'userable.tel.max' => '固定電話が長すぎます(100文字まで)。',
            'userable.fax.max' => 'FAXが長すぎます(100文字まで)。',
            'userable.email.max' => 'メールアドレスが長すぎます(255文字まで)。',
            'userable.user_ext.emergency_contact_column.max' => '緊急連絡先カラムが長すぎます(100文字まで)。',
            'userable.zip_code.max' => '郵便番号が長すぎます(32文字まで)。',
            'userable.address1.max' => '住所が長すぎます(100文字まで)。',
            'userable.address2.max' => 'ビル・建物名が長すぎます(100文字まで)。',
            'userable.passport_number.max' => '旅券番号が長すぎます(100文字まで)。',
            'userable.passport_issue_date.date' => '旅券発行日の入力形式が正しくありません(YYYY-MM-DD)。',
            'userable.passport_expiration_date.date' => '旅券有効期限の入力形式が正しくありません(YYYY-MM-DD)。',
            'userable.workspace_name.max' => '名称(勤務先/学校)が長すぎます(100文字まで)。',
            'userable.workspace_address.max' => '住所(勤務先/学校)が長すぎます(200文字まで)。',
            'userable.workspace_tel.max' => '電話番号(勤務先/学校)が長すぎます(100文字まで)。',
            'userable.workspace_note.max' => '備考(勤務先/学校)が長すぎます(1000文字まで)。',
            'userable.user_ext.dm.in' => 'DMの指定が不正です。',
            'userable.user_ext.note.max' => '備考が長すぎます(1000文字まで)。',
            'user_visas.*.number.max' => '番号(ビザ情報)が長すぎます(100文字まで)。',
            'user_visas.*.kind.max' => '種別(ビザ情報)が長すぎます(100文字まで)。',
            'user_visas.*.issue_date.date' => '発行日(ビザ情報)の入力形式が正しくありません(YYYY-MM-DD)。',
            'user_visas.*.expiration_date.date' => '有効期限(ビザ情報)の入力形式が正しくありません(YYYY-MM-DD)。',
            'user_visas.*.note.max' => '備考(ビザ情報)が長すぎます(1000文字まで)。',
            'user_mileages.*.airline.max' => '航空会社名(マイレージ)が長すぎます(100文字まで)。',
            'user_mileages.*.card_number.max' => 'カード番号(マイレージ)が長すぎます(100文字まで)。',
            'user_mileages.*.note.max' => 'カード番号(マイレージ)が長すぎます(1000文字まで)。',
            'user_member_cards.*.card_name.max' => 'カード名(メンバーズカード)が長すぎます(100文字まで)。',
            'user_member_cards.*.card_number.max' => 'カード番号(メンバーズカード)が長すぎます(100文字まで)。',
            'user_member_cards.*.note.max' => '備考(メンバーズカード)が長すぎます(1000文字まで)。',
        ];
    }
}
