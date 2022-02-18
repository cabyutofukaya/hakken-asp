<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class InflowStoreRequest extends FormRequest
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
            'site_name' => 'required|string|max:32',
            'url' => 'nullable|url'
        ];
    }

    public function messages()
    {
        return [
            'site_name.required' => 'サイト名は必須です',
            'url.url' => 'URLの入力が不正です'
        ];
    }
}
