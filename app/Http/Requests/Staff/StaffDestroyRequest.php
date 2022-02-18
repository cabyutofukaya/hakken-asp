<?php

namespace App\Http\Requests\Staff;

use App\Services\StaffService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StaffDestroyRequest extends FormRequest
{
    public function __construct(StaffService $staffService)
    {
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
                    return $fail("システム管理者は削除できません。");
                    }
                }
            ],
        ];
    }

    public function messages()
    {
        return [
            'account.required' => 'スタッフアカウントは必須です。',
        ];
    }
}
