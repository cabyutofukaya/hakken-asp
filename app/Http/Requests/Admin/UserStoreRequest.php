<?php

namespace App\Http\Requests\Admin;

use Auth;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserStoreRequest extends FormRequest
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
            'agency_id' => 'required|integer',
            'last_name' => 'required',
            'first_name' => 'required',
            'email' => ['required','email',Rule::unique('users', 'email')->whereNull('deleted_at')],
            'password' => 'required|string|min:8',
            'note' => 'nullable|max:2000',
            // 'inflow_id' => 'nullable',
            'status' => ['required',Rule::in(array_values(config("consts.users.STATUS_LIST")))]
        ];
    }
    
    public function messages()
    {
        return [
        'agency_id.required' => '従属会社は必須です',
        'last_name.required' => '姓は必須です',
        'first_name.required' => '名は必須です',
        'email.required' => 'メールアドレスは必須です',
        'email.email' => 'メールアドレスの入力形式が正しくありません',
        'email.unique' => 'そのメールアドレスはすでに登録されています',
        'password.required' => 'パスワードは必須です',
        'password.string' => 'パスワードは文字列で入力してください',
        'password.min' => 'パスワードは8文字以上で設定してください',
        'status.required' => '状態は必須です',
        'note.max' => '備考が長すぎます'
    ];
    }
}
