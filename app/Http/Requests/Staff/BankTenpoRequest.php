<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class BankTenpoRequest extends FormRequest
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
            'kinyu_code' => $this->request->get('kinyu_code'),
            'tenpo_code' => $this->request->get('tenpo_code'),
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
            'kinyu_code' => 'nullable|numeric',
            'tenpo_code' => 'nullable|numeric'
        ];
    }
    
    public function messages()
    {
        return [
            'kinyu_code.numeric' => '金融機関コードは半角数字で入力してください。',
            'tenpo_code.numeric' => '支店コードは半角数字で入力してください。',
        ];
    }
}
