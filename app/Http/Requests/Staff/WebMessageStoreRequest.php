<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class WebMessageStoreRequest extends FormRequest
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
            'message' => 'nullable|max:2000', // 一応文字数制限
            'send_at' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'message.max' => 'メッセージが長すぎます(2000文字まで)。',
            'send_at.required' => '送信日時は必須です。',
        ];
    }
}
