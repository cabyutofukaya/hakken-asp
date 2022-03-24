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
            'reception' => $this->reception,
            'id' => $this->id,
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
            'reception' => 'required',
            'id' => 'required',
            'reserve.updated_at' => 'required',
        ];
    }
    
    public function messages()
    {
        return [
            'reception.required' => '受付種別は必須です。',
            'id.required' => '参加者IDは必須です。',
            'reserve.updated_at.required' => '予約更新日時は必須です。',
        ];
    }
}
