<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class DocumentCommonStoretRequest extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|max:100',
            'description' => 'nullable|max:100',
            'setting' => 'nullable|array',
            'company_name' => 'nullable|max:30',
            'supplement1' => 'nullable|max:300',
            'supplement2' => 'nullable|max:300',
            'zip_code' => 'nullable|regex:/^[0-9]+$/|max:7',
            'address1' => 'nullable|max:100',
            'address2' => 'nullable|max:100',
            'tel' => 'nullable|max:32',
            'fax' => 'nullable|max:32',
            'setting' => 'nullable|array',
        ];
    }
    
    public function messages()
    {
        return [
            'name.required' => 'テンプレート名は必須です。',
            'name.max' => 'テンプレート名が長過ぎます(100文字まで)。',
            'description.max' => '説明文が長過ぎます(100文字まで)。',
            'company_name.max' => '自社名が長過ぎます(30文字まで)。',
            'supplement1.max' => '補足1が長過ぎます(300文字まで)。',
            'supplement2.max' => '補足2が長過ぎます(300文字まで)。',
            'zip_code.regex' => '郵便番号は半角数字で入力してください(ハイフンなし)。',
            'zip_code.max' => '郵便番号が長過ぎます(7文字まで)。',
            'address1.max' => '住所1が長過ぎます(100文字まで)。',
            'address2.max' => '住所2が長過ぎます(100文字まで)。',
            'tel.max' => '電話番号が長過ぎます(32文字まで)。',
            'fax.max' => 'FAXが長過ぎます(32文字まで)。',
            "setting.array" => '項目設定のデータ形式が不正です。',
        ];
    }
}
