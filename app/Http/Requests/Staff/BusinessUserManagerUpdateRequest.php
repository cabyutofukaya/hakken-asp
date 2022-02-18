<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BusinessUserManagerUpdateRequest extends FormRequest
{
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
            'id' => $this->businessUserManager,
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
            'id' => 'required',
            'user_number' => 'required',
            'name' => 'nullable|max:100',
            'name_roman' => 'nullable|max:100',
            'sex' => ['nullable',Rule::in(array_values(config("consts.users.SEX_LIST")))],
            'department_name' => 'nullable|max:100',
            'email' => 'nullable|max:100',
            'tel' => 'nullable|max:100',
            'dm' => ['nullable',Rule::in(array_values(config("consts.business_user_managers.DM_LIST")))],
            'note' => 'nullable|max:1000',
            'updated_at' => 'required',
        ];
    }
    
    public function messages()
    {
        return [
            'id.required' => '取引先担当者情報IDは必須です。',
            'user_number.required' => '顧客番号は必須です。',
            'name.max' => '担当者名(取引先担当者情報)が長すぎます(100文字まで)。',
            'name_roman.max' => '担当者名ローマ字(取引先担当者情報)が長すぎます(100文字まで)。',
            'sex.in' => '性別(取引先担当者情報)の指定が不正です。',
            'department_name.max' => '部署名(取引先担当者情報)が長すぎます(100文字まで)。',
            'email.max' => 'メールアドレス(取引先担当者情報)が長すぎます(100文字まで)。',
            'tel.max' => '電話番号(取引先担当者情報)が長すぎます(100文字まで)。',
            'dm.in' => 'DM(取引先担当者情報)の指定が不正です。',
            'note.max' => '備考(取引先担当者情報)が長すぎます(1000文字まで)。',
            'updated_at.required' => '更新日時は必須です。',
        ];
    }
}
