<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class DirectionUpdateRequest extends FormRequest
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
            'uuid' => $this->uuid,
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
            'uuid' => 'required',
            // 'code' => 'required|max:100',
            'name' => 'nullable|max:120',
        ];
    }
    
    public function messages()
    {
        return [
            'uuid.required' => '方面IDは必須です。',
            // 'code.required' => '方面コードは必須です。',
            // 'code.max' => '方面コードが長過ぎます(100文字まで)。',
            'name.max' => '方面名称が長過ぎます(120文字まで)。',
        ];
    }
}
