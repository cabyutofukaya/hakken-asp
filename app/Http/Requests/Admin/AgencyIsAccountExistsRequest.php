<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AgencyIsAccountExistsRequest extends FormRequest
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
            'account' => ['required','max:32','regex:/^[a-zA-Z0-9]+$/',Rule::unique('agencies', 'account')], // 論理削除も含めてチェック
        ];
    }
    
    public function messages()
    {
        return [
        'account.required' => 'アカウントは必須です',
        'account.max' => 'アカウントは32文字以内で入力してください',
        'account.regex' => 'アカウントは半角英数で入力してください',
        'account.unique' => 'そのアカウントは以前登録されていたか、既に登録済みです',
        ];
    }
}
