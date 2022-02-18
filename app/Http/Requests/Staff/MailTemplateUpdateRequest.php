<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class MailTemplateUpdateRequest extends FormRequest
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
            'subject' => 'required|max:100',
            'body' => 'nullable|max:3000',
            'setting' => 'nullable|array',
        ];
    }
    
    public function messages()
    {
        return [
            'name.required' => 'テンプレート名は必須です。',
            'name.max' => 'テンプレート名が長過ぎます(100文字まで)。',
            'description.max' => '説明文が長過ぎます(100文字まで)。',
            'subject.required' => '件名は必須です。',
            'subject.max' => '件名が長過ぎます(100文字まで)。',
            'body.max' => '本文が長過ぎます(3000文字まで)。',
        ];
    }
}
