<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerSearchRequest extends FormRequest
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
            'participant_type' => $this->participant_type,
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
            'participant_type' => ['required',Rule::in(array_values(config("consts.reserves.PARTICIPANT_TYPE_LIST")))],
            'user_number' => 'nullable',
            'name' => 'nullable',
            'get_deleted' => 'nullable', // 削除済みを検索するか否か
        ];
    }
    
    public function messages()
    {
        return [
            'participant_type.required' => '顧客種別は必須です。',
        ];
    }
}
