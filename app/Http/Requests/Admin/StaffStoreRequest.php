<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StaffStoreRequest extends FormRequest
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
            'agency_id' => $this->agency,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $agency = $this->route('agency');

        return [
            'agency_id' => 'required|integer',
            'account' => ['required',Rule::unique('staffs', 'account')->where('agency_id', $agency)->whereNull('deleted_at')],
            'last_name' => 'nullable',
            'first_name' => 'nullable',
            'email' => ['nullable','email',Rule::unique('staffs', 'email')->where('agency_id', $agency)->whereNull('deleted_at')],
            'password' => 'required|string|min:8',
            'status' => ['required',Rule::in(array_values(config("consts.staffs.STATUS_LIST")))],
            'role_id' => ['required','exists:roles,id']
        ];
    }
    
    public function messages()
    {
        return [
        'agency_id.required' => '会社IDは必須です',
        'agency_id.integer' => '会社IDの入力が不正です',
        'account.required' => 'アカウントは必須です',
        'account.unique' => 'そのアカウントはすでに登録されています',
        'email.email' => 'メールアドレスの入力をご確認ください',
        'email.unique' => 'そのメールアドレスはすでに登録されています',
        'password.required' => 'パスワードは必須です',
        'password.string' => 'パスワードは文字列で入力してください',
        'password.min' => 'パスワードは8文字以上で設定してください',
        'status.required' => '状態は必須です',
        'role_id.required' => '役割は必須です',
        'role_id.exists' => '役割IDが不正です'
    ];
    }
}
