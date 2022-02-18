<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class ReserveItineraryDestroyRequest extends FormRequest
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
            'reserve_number' => $this->reserveNumber,
            'control_number' => $this->controlNumber,
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
            'estimate_number' => 'nullable',
            'reserve_number' => 'nullable',
            'control_number' => 'required',
        ];
    }
    
    public function messages()
    {
        return [
            'control_number.required' => '行程番号は必須です。',
        ];
    }
}
