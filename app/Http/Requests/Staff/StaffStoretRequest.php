<?php

namespace App\Http\Requests\Staff;

use App\Rules\CheckPassword;
use App\Services\AgencyRoleService;
use App\Services\StaffService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StaffStoretRequest extends FormRequest
{
    public function __construct(AgencyRoleService $agencyRoleService, StaffService $staffService)
    {
        $this->agencyRoleService = $agencyRoleService;
        $this->staffService = $staffService;
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
        $agency = auth('staff')->user()->agency;

        return [
            'account' => ['required','regex:/^[a-zA-Z0-9_\-]+$/',
                function ($attribute, $value, $fail) use ($agency) {
                    if ($this->staffService->isAccountExists($agency->id, $value)
                    ) {
                        return $fail("そのアカウントはすでに使用されています。");
                    }
                },
                function ($attribute, $value, $fail) use ($agency) {
                    if ($agency->staffs->count() >= $agency->number_staff_allowed) {
                        return $fail("ユーザー作成数上限に達してるためユーザーを追加できません。");
                    }
                }
            ],
            'name' => 'required',
            'agency_role_id' => ['required',
                function ($attribute, $value, $fail) {
                    if (!in_array($value, $this->agencyRoleService->getIdsByAgencyId(auth('staff')->user()->agency->id))) {
                        return $fail("権限IDの指定が不正です。");
                    }
                }
            ],
            'password' => ['required','confirmed',new CheckPassword()],
            'password_confirmation' => 'required',
            'email' => 'required|email',
            'status' => ['required',Rule::in(array_values(config("consts.staffs.STATUS_LIST")))],
        ];
    }
    
    public function messages()
    {
        return [
            'account.required' => 'アカウントIDは必須です。',
            'account.regex' => 'アカウントIDは半角英数文字(a-z,A-Z,0-9,-_)で入力してください。',
            'name.required' => 'ユーザー名は必須です。',
            'agency_role_id.required' => 'ユーザー権限は必須です。',
            'password.required' => 'パスワードは必須です。',
            'password.min' => 'パスワードは8文字以上で設定してください。',
            'password.confirmed' => 'パスワードと確認用パスワードが一致していません。',
            'password_confirmation.required' => '確認用パスワードは必須です。',
            'email.required' => 'メールアドレスは必須です。',
            'email.email' => 'メールアドレスの入力形式が不正です。',
            'status.required' => 'アカウント状態は必須です。',
            'status.in' => 'アカウント状態の指定が不正です。',
        ];
    }
}
