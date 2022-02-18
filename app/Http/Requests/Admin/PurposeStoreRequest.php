<?php

namespace App\Http\Requests\Admin;

use Auth;
use App\Models\Purpose;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurposeStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return Auth::guard('admin')->user()->can('create', Purpose::class); // 権限チェック
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
            'seq' => 'integer',
        ];
    }
    
    public function messages()
    {
        return [
        'name.required' => '名称は必須です',
        'seq.integer' => '順番の指定が不正です',
    ];
    }
}
