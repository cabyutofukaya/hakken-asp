<?php

namespace App\Http\Requests\Staff;

use App\Services\PrefectureService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BusinessUserUpdateRequest extends FormRequest
{
    public function __construct(PrefectureService $prefectureService)
    {
        $this->prefectureService = $prefectureService;
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
            'name' => 'nullable|max:32',
            'name_kana' => 'nullable|max:32',
            'name_roman' => 'nullable|max:100',
            'tel' => 'nullable|max:100',
            'fax' => 'nullable|max:100',
            'email' => 'nullable|max:255',
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
            'pay_altogether' => ['nullable',Rule::in(array_values(config("consts.business_users.PAY_ALTOGETHER_LIST")))],
            'note' => 'nullable|max:1000',
            'business_user_managers' => 'required|array',
            'business_user_managers.*.name' => 'nullable|max:100',
            'business_user_managers.*.name_roman' => 'nullable|max:100',
            'business_user_managers.*.sex' => ['nullable',Rule::in(array_values(config("consts.users.SEX_LIST")))],
            'business_user_managers.*.department_name' => 'nullable|max:100',
            'business_user_managers.*.email' => 'nullable|max:100',
            'business_user_managers.*.tel' => 'nullable|max:100',
            'business_user_managers.*.dm' => ['nullable',Rule::in(array_values(config("consts.business_user_managers.DM_LIST")))],
            'business_user_managers.*.note' => 'nullable|max:1000',
        ];
    }
    
    public function messages()
    {
        return [
            'user_number.required' => '法人顧客番号は必須です。',
            'name.max' => '法人名が長すぎます(32文字まで)。',
            'name_kana.max' => '法人名(カナ)が長すぎます(32文字まで)。',
            'name_roman.max' => '法人名(英語表記)が長すぎます(100文字まで)。',
            'tel.max' => '電話番号が長すぎます(100文字まで)。',
            'fax.max' => 'FAXが長すぎます(100文字まで)。',
            'email.max' => 'メールアドレスが長すぎます(255文字まで)。',
            'zip_code.max' => '郵便番号が長すぎます(32文字まで)。',
            'address1.max' => '住所が長すぎます(100文字まで)。',
            'address2.max' => 'ビル・建物名が長すぎます(100文字まで)。',
            'pay_altogether.in' => '一括支払契約の指定が不正です。',
            'note.max' => '備考が長すぎます(1000文字まで)。',
            'business_user_managers.required' => '取引先担当者を登録してください。',
            'business_user_managers.*.name.max' => '担当者名(取引先担当者情報)が長すぎます(100文字まで)。',
            'business_user_managers.*.sex.in' => '担当者(取引先担当者情報)の性別値が不正です。',
            'business_user_managers.*.department_name.max' => '部署名(取引先担当者情報)が長すぎます(100文字まで)。',
            'business_user_managers.*.email.max' => 'メールアドレス(取引先担当者情報)が長すぎます(100文字まで)。',
            'business_user_managers.*.tel.max' => '電話番号(取引先担当者情報)が長すぎます(100文字まで)。',
            'business_user_managers.*.dm.in' => 'DM(取引先担当者情報)の指定が不正です。',
            'business_user_managers.*.note.max' => '備考(取引先担当者情報)が長すぎます(1000文字まで)。',
        ];
    }
}
