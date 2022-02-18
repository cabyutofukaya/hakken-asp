<?php

namespace App\Http\Requests\Staff;

use App\Services\AgencyRoleService;
use App\Services\StaffService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StaffStatusUpdateRequest extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'account' => ['required',
                function ($attribute, $value, $fail) {
                    if ($this->staffService->isMasterAccount(auth('staff')->user()->agency->id, $value)) {
                    return $fail("システム管理者のステータスは編集できません。");
                    }
                }
            ],
            'status' => ['required',Rule::in(array_values(config("consts.staffs.STATUS_LIST")))]
        ];
    }
    
    public function messages()
    {
        return [
            'account.required' => 'アカウントは必須です。',
            'status.required' => 'ステータスは必須です。',
            'status.in' => 'ステータスの値が不正です。',
        ];
    }
}
