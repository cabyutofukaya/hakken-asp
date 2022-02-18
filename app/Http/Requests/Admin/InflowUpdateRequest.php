<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class InflowUpdateRequest extends FormRequest
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
            'id' => $this->inflow,
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
            'id' => 'required',
            'site_name' => 'required|string|max:32',
            'url' => 'nullable|url'
        ];
    }

    public function messages()
    {
        return [
            'id.required' => '流入サイトIDは必須です',
            'site_name.required' => 'サイト名は必須です',
            'url.url' => 'URLの入力が不正です'
        ];
    }
}
