<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StaffUpdateRequest extends FormRequest
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
            'id' => $this->staff,
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
            'id' => 'required|integer',
            'last_name' => 'nullable',
            'first_name' => 'nullable',
            'email' => ['nullable','email',Rule::unique('staffs', 'email')->whereNot('agency_id', $agency)->whereNull('deleted_at')],
            'password' => 'nullable|string|min:8',
            'status' => ['required',Rule::in(array_values(config("consts.staffs.STATUS_LIST")))],
            'role_id' => ['required','exists:roles,id']
        ];
    }
    
    public function messages()
    {
        return [
        'agency_id.required' => '会社IDは必須です',
        'agency_id.integer' => '会社IDの入力が不正です',
        'id.required' => 'スタッフIDは必須です',
        'id.integer' => 'スタッフIDの入力が不正です',
        'email.email' => 'メールアドレスの入力をご確認ください',
        'email.unique' => 'そのメールアドレスはすでに登録されています',
        'password.string' => 'パスワードは文字列で入力してください',
        'password.min' => 'パスワードは8文字以上で設定してください',
        'status.required' => '状態は必須です',
        'role_id.required' => '役割は必須です',
        'role_id.exists' => '役割IDが不正です'
    ];
    }
}
