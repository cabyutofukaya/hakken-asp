<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AgencyUpdateStatusRequest extends FormRequest
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
            'status' => ['required',Rule::in(array_values(config("consts.agencies.STATUS_LIST")))]
        ];
    }
    
    public function messages()
    {
        return [
            'status.required' => 'ステータスは必須です。',
            'status.in' => 'ステータスの値が不正です。',
        ];
    }
}
