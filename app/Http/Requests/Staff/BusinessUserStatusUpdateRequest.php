<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BusinessUserStatusUpdateRequest extends FormRequest
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
            'user_number' => $this->userNumber,
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
            'user_number' => 'required',
            'status' => ['required',Rule::in(array_values(config("consts.business_users.STATUS_LIST")))]
        ];
    }
    
    public function messages()
    {
        return [
            'user_number.required' => '顧客番号は必須です。',
            'status.required' => 'ステータスは必須です。',
            'status.in' => 'ステータスの値が不正です。',
        ];
    }
}
