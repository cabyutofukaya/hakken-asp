<?php

namespace App\Http\Requests\Admin;

use Auth;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
{
    public function __construct(UserService $userService)
    {
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
            'id' => $this->user,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $id = $this->route('user');
        return [
            'id' => 'required',
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            'email' => ['required','email',Rule::unique('users', 'email')->whereNot('id', $id)->whereNull('deleted_at')],
            'password' => 'nullable|string|min:8',
            // 'inflow_id' => 'nullable',
            'note' => 'nullable|max:2000',
            'status' => ['required',Rule::in(array_values(config("consts.users.STATUS_LIST")))]
        ];
    }
    
    public function messages()
    {
        return [
            'id.required' => 'IDは必須です',
            'agency_id.required' => '従属会社は必須です',
            'first_name.required' => '名は必須です',
            'last_name.required' => '姓は必須です',
            'email.required' => 'メールアドレスは必須です',
            'email.email' => 'メールアドレスの入力形式が正しくありません',
            'email.unique'  => 'そのメールアドレスはすでに使われています',
            'password.string' => 'パスワードは文字列で入力してください',
            'password.min' => 'パスワードは8文字以上で設定してください',
            'note.max' => '備考が長すぎます',
            'status.required' => '状態は必須です',
        ];
    }
}
