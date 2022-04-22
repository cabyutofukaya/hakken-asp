<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserCustomItemStoreListRequest extends FormRequest
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
            'user_custom_category_id' => ['required', 'exists:user_custom_categories,id'],
            'display_position' => 'nullable',
            'name' => 'required',
            'list' => 'nullable|array',
        ];
    }
    
    public function messages()
    {
        return [
        'user_custom_category_id.required' => 'カテゴリは必須です。',
        'user_custom_category_id.exists' => 'カテゴリの指定が不正です。',
        'name.required' => 'リスト項目名は必須です。',
    ];
    }
}
