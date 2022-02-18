<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EstimateConsentRequest extends FormRequest
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
            'request_number' => $this->requestNumber,
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
            'request_number' => 'required',
            'message' => 'nullable|max:50'
        ];
    }
    
    public function messages()
    {
        return [
            'request_number.required' => '依頼番号は必須です。',
            'message.max' => '一言メッセージが長すぎます(50文字まで)。',
        ];
    }
}
