<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\AgencyRoleService;
use App\Services\StaffService;

class AgencyRoleDestroyRequest extends FormRequest
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
            'id' => $this->role,
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
            'id' => ['required',
                function ($attribute, $value, $fail) {
                    if ($this->agencyRoleService->getMasterRoleId(auth('staff')->user()->agency->id) == $value) {
                        return $fail("システム管理者の権限は削除できません。");
                    }
                },
                function ($attribute, $value, $fail) {
                    if ($this->staffService->getCountByAgencyRoleId($value) >= 1) {
                        return $fail("権限を利用しているユーザーがいるため削除できません。");
                    }
                }
            ],
        ];
    }
    
    public function messages()
    {
        return [
        ];
    }
}
