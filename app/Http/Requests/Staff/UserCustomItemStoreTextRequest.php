<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserCustomItemStoreTextRequest extends FormRequest
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
            'type' => $this->type,
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
            'user_custom_category_id' => ['required', 'exists:user_custom_categories,id'],
            'display_position' => 'nullable',
            'name' => 'required',
            'input_type' => 'required',
        ];
    }
    
    public function messages()
    {
        return [
            'user_custom_category_id.required' => 'カテゴリパラメータは必須です。',
            'user_custom_category_id.exists' => 'カテゴリパラメータが不正です。',
            'user_custom_category_item_id.required' => 'カテゴリ項目パラメータは必須です。',
            'user_custom_category_item_id.exists' => 'カテゴリ項目パラメータが不正です。',
            'name.required' => '項目名は必須です。',
            'input_type.required' => '入力形式は必須です。',
        ];
    }
}
