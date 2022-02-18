<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class WebModelcourseShowUpdateRequest extends FormRequest
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
            'show' => 'required|boolean',
        ];
    }
    
    public function messages()
    {
        return [
            'show.required' => '表示フラグは必須です。',
            'show.boolean' => '表示フラグの指定が不正です。',
        ];
    }
}
