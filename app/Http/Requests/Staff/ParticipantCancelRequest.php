<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class ParticipantCancelRequest extends FormRequest
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
            'application_step' => $this->applicationStep,
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
        $managerId = $this->manager_id;

        return [
            'control_number' => 'required',
            'application_step' => 'required',
        ];
    }
    
    public function messages()
    {
        return [
            'application_step.required' => '申し込み種別は必須です。',
            'control_number.required' => '予約/見積番号は必須です。',
        ];
    }
}
