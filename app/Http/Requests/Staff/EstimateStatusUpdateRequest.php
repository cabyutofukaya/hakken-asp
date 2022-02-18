<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class EstimateStatusUpdateRequest extends FormRequest
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
            'estimate_number' => $this->estimateNumber,
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
            'estimate_number' => 'required',
            'status' => 'required',
        ];
    }
    
    public function messages()
    {
        return [
            'estimate_number.required' => '見積番号は必須です。',
            'status.required' => 'ステータスは必須です。',
        ];
    }
}
