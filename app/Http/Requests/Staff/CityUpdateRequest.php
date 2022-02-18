<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class CityUpdateRequest extends FormRequest
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
            'id' => $this->city,
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
            'id' => 'required',
            'v_area_uuid' => ['nullable','exists:v_areas,uuid'],
            'name' => 'nullable|max:120',
        ];
    }
    
    public function messages()
    {
        return [
            'id.required' => '都市・空港IDは必須です。',
            'v_area_uuid.exists' => '「国・地域」の値が不正です。',
            'name.max' => '都市・空港名称が長過ぎます(120文字まで)。',
        ];
    }
}
