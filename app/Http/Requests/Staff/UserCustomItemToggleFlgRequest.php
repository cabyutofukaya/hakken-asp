<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserCustomItemToggleFlgRequest extends FormRequest
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
            'id' => 'required',
            'flg' => 'nullable|boolean',
        ];
    }
    
    public function messages()
    {
        return [
            'id.required' => 'カスタム項目IDは必須です。',
            'flg.boolean' => 'フラグの値が不正です。',
        ];
    }
}
