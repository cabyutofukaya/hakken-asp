<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class UserMemberCardStoreRequest extends FormRequest
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
            'user_number' => $this->userNumber,
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
            'user_number' => 'required',
            'card_name' => 'nullable|max:100',
            'card_number' => 'nullable|max:100',
            'note' => 'nullable|max:1000',
        ];
    }
    
    public function messages()
    {
        return [
            'user_number.required' => '顧客番号は必須です。',
            'card_name.max' => 'カード名(マイレージ情報)が長すぎます(100文字まで)。',
            'card_number.max' => 'カード番号(マイレージ情報)が長すぎます(100文字まで)。',
            'issue_date.date' => '発行日(ビザ情報)の入力形式。',
            'note.max' => '備考(マイレージ情報)が長すぎます(1000文字まで)。',
        ];
    }
}
