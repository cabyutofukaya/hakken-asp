<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class AgencyRoleStoreRequest extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|max:100',
            'description' => 'nullable|max:100',
            'authority' => 'nullable|array'
        ];
    }
    
    public function messages()
    {
        return [
            'name.required' => '権限名称は必須です。',
            'name.max' => '権限名称が長すぎます。',
            'description.max' => '説明が長すぎます。',
        ];
    }
}
