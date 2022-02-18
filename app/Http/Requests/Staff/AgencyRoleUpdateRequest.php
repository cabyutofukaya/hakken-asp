<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use App\Services\AgencyRoleService;

class AgencyRoleUpdateRequest extends FormRequest
{
    public function __construct(AgencyRoleService $agencyRoleService)
    {
        $this->agencyRoleService = $agencyRoleService;
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
                        return $fail("システム管理者の権限は編集できません");
                    }
                }
            ],
            'name' => 'required|max:100',
            'description' => 'nullable|max:100',
            'authority' => 'nullable|array'
        ];
    }
    
    public function messages()
    {
        return [
            'id.required' => 'IDは必須です',
            'name.required' => '権限名称は必須です',
            'name.max' => '権限名称が長すぎます',
            'description.max' => '説明が長すぎます',
        ];
    }
}
