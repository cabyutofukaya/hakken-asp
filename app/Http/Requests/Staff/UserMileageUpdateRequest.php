<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class UserMileageUpdateRequest extends FormRequest
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
            'id' => $this->userMileage,
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
            'id' => 'required',
            'user_number' => 'required',
            // 'airline_id' => ['nullable','exists:airlines,id'],
            'card_number' => 'nullable|max:100',
            'note' => 'nullable|max:1000',
        ];
    }
    
    public function messages()
    {
        return [
            'id.required' => 'マイレージ情報IDは必須です。',
            'user_number.required' => '顧客番号は必須です。',
            // 'airline_id.exists' => '航空会社の入力が正しくありません。',
            'card_number.max' => 'カード番号(マイレージ情報)が長すぎます(100文字まで)。',
            'note.max' => '備考(マイレージ情報)が長すぎます(1000文字まで)。',
        ];
    }
}
