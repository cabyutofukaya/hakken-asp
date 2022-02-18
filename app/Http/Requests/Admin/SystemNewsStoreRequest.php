<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SystemNewsStoreRequest extends FormRequest
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
            'regist_date' => 'required|date',
            'title' => 'required',
            'content' => 'required|max:300',
        ];
    }
    
    public function messages()
    {
        return [
            'regist_date.required' => '登録日は必須です',
            'regist_date.date' => '登録日の入力形式が不正です',
            'title.required' => '通知内容は必須です',
            'content.required' => '本文は必須です',
            'content.max' => '本文が長すぎます(300文字以内)',
        ];
    }
}
