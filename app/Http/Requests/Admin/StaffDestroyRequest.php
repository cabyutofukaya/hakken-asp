<?php

namespace App\Http\Requests\Admin;

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
        return [
            'agency_id' => 'required|integer',
            'id' => ['required','integer',
                function ($attribute, $value, $fail) {
                    if ($this->staffService->isMasterId((int)$value)) {
                        return $fail("システム管理者は削除できません");
                    }
                }
            ],
        ];
    }

    public function messages()
    {
        return [
            'agency_id.required' => '会社IDは必須です',
            'agency_id.integer' => '会社IDの入力形式が正しくありません',
            'id.required' => 'スタッフIDは必須です',
            'id.integer' => 'スタッフIDの入力形式が正しくありません',
        ];
    }
}
