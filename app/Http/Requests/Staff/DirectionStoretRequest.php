<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DirectionStoretRequest extends FormRequest
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
        $agencyId = auth("staff")->user()->agency_id;

        return [
            'code' => ['required','max:100',Rule::unique('directions', 'code')->where('agency_id', $agencyId)],
            'name' => 'nullable|max:120',
        ];
    }
    
    public function messages()
    {
        return [
            'code.required' => '方面コードは必須です。',
            'code.max' => '方面コードが長過ぎます(100文字まで)。',
            'code.unique' => 'すでに登録済みか、過去に使用された方面コードは登録できません。',
            'name.max' => '方面名称が長過ぎます(120文字まで)。',
        ];
    }
}
