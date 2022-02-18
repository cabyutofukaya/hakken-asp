<?php

namespace App\Http\Requests\Staff;

use App\Rules\CheckPassword;
use App\Services\AgencyRoleService;
use App\Services\StaffService;
use Illuminate\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StaffUpdateRequest extends FormRequest
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

    public function validationData()
    {
        return array_merge($this->request->all(), [
            'account' => $this->account,
        ]);
    }

    public function withValidator(Validator $validator)
    {
        // マスターユーザーでない場合は権限指定必須
        $validator->sometimes('agency_role_id', ['required',
            function ($attribute, $value, $fail) {
                if(!in_array($value, $this->agencyRoleService->getIdsByAgencyId(auth('staff')->user()->agency->id))){
                    return $fail("権限IDの指定が不正です。");
                }
            }
            ], function ($input) {
                return $input->is_master == 0;
        });

        // マスターユーザーの場合は権限指定不要
        $validator->sometimes('agency_role_id', ['nullable',
            function ($attribute, $value, $fail) {
                if(!in_array($value, $this->agencyRoleService->getIdsByAgencyId(auth('staff')->user()->agency->id))){
                    return $fail("権限IDの指定が不正です。");
                }
            }
            ], function ($input) {
                return $input->is_master == 1;
        });
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'account' => 'required',
            'name' => 'required',
            'is_master' => 'required|boolean',
            'password' => ['nullable','confirmed',new CheckPassword()],
            'password_confirmation' => 'nullable',
            'email' => 'required|email',
            'updated_at' => 'nullable',
        ];
    }
    
    public function messages()
    {
        return [
            'account.required' => 'アカウントIDは必須です。',
            'name.required' => 'ユーザー名は必須です。',
            'agency_role_id.required' => 'ユーザー権限は必須です。',
            'is_master' => 'マスター判定パラメータは必須です。',
            'password.min' => 'パスワードは8文字以上で設定してください。',
            'password.confirmed' => 'パスワードと確認用パスワードが一致していません。',
            'email.required' => 'メールアドレスは必須です。',
            'email.email' => 'メールアドレスの入力形式が不正です。',
        ];
    }
}
