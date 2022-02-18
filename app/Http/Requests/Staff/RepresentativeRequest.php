<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RepresentativeRequest extends FormRequest
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
            'control_number' => $this->controlNumber,
            'application_step' => $this->applicationStep,
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
            'application_step' => 'required',
            'control_number' => 'required',
            'participant_id' => 'required',
            'updated_at' => 'nullable',
        ];
    }
    
    public function messages()
    {
        return [
            'application_step.required' => '申し込み種別は必須です。',
            'control_number.required' => '予約/見積番号は必須です。',
            'participant_id.required' => '参加者IDは必須です。',
        ];
    }
}
