<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class ReserveNoCancelChargeCancelRequest extends FormRequest
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
            'updated_at' => 'required',
        ];
    }
    
    public function messages()
    {
        return [
            'updated_at.required' => '予約更新日時は必須です。',
        ];
    }
}
