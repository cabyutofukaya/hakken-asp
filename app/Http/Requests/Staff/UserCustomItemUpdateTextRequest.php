<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserCustomItemUpdateTextRequest extends FormRequest
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
            'name' => 'nullable',
            'display_position' => 'nullable',
            'input_type' => ['required',Rule::in(array_values(config("consts.user_custom_items.INPUT_TYPE_TEXT_LIST")))],
        ];
    }
    
    public function messages()
    {
        return [
            'input_type.required' => '入力形式は必須です。',
            'input_type.in' => '入力形式の値が不正です。',
        ];
    }
}
