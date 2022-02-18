<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AreaStoretRequest extends FormRequest
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
            'code' => ['required','max:100',Rule::unique('areas', 'code')->where('agency_id', $agencyId)],
            'v_direction_uuid' => ['nullable','exists:v_directions,uuid'],
            'name' => 'nullable|max:120',
            'name_en' => 'nullable|max:120',
        ];
    }
    
    public function messages()
    {
        return [
            'code.required' => '国・地域コードは必須です。',
            'code.max' => '国・地域コードが長過ぎます(100文字まで)。',
            'code.unique' => 'すでに登録済みか、過去に使用された国・地域コードは登録できません。',
            'v_direction_uuid.exists' => '「方面」の値が不正です。',
            'name.max' => '国・地域名称が長過ぎます(120文字まで)。',
            'name_en.max' => '国・地域名称(英)が長過ぎます(120文字まで)。',
        ];
    }
}
