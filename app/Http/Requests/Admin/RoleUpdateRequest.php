<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleUpdateRequest extends FormRequest
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
            'id' => 'required',
            'name' => 'required',
            'name_en' => 'required|regex:/^[a-zA-Z0-9]+$/',
            'authority' => 'nullable|array'
        ];
    }
    
    public function messages()
    {
        return [
        'id.required' => 'IDは必須です',
        'name.required' => '名称は必須です',
        'name_en.required' => '名称（英語）は必須です',
        'name_en.regex' => '名称（英語）は半角英数で入力してください',
    ];
    }
}
