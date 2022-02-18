<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class AreaUpdateRequest extends FormRequest
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
            'uuid' => $this->uuid,
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
            'uuid' => 'required',
            'v_direction_uuid' => ['nullable','exists:v_directions,uuid'],
            'name' => 'nullable|max:120',
            'name_en' => 'nullable|max:120',
        ];
    }
    
    public function messages()
    {
        return [
            'uuid.required' => '国・地域UUIDは必須です。',
            'v_direction_uuid.exists' => '「方面」の値が不正です。',
            'name.max' => '国・地域名称が長過ぎます(120文字まで)。',
            'name_en.max' => '国・地域名称(英)が長過ぎます(120文字まで)。',
        ];
    }
}
