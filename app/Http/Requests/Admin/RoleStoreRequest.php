<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoleStoreRequest extends FormRequest
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
            'name' => 'required',
            'name_en' => 'required|regex:/^[a-zA-Z0-9]+$/',
            'authority' => 'nullable|array'
        ];
    }
    
    public function messages()
    {
        return [
        'name.required' => '名称は必須です',
        'name_en.required' => '名称（英語）は必須です',
        'name_en.regex' => '名称（英語）は半角英数で入力してください',
    ];
    }
}
