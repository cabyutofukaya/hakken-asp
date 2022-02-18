<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CityStoretRequest extends FormRequest
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
            'code' => ['required','max:100',Rule::unique('cities', 'code')->where('agency_id', $agencyId)],
            'v_area_uuid' => ['nullable','exists:v_areas,uuid'],
            'name' => 'nullable|max:120',
        ];
    }
    
    public function messages()
    {
        return [
            'code.required' => '都市・空港コードは必須です。',
            'code.max' => '都市・空港コードが長過ぎます(100文字まで)。',
            'code.unique' => 'すでに登録済みか、過去に使用された都市・空港コードは登録できません。',
            'v_area_uuid.exists' => '「国・地域」の値が不正です。',
            'name.max' => '都市・空港名称が長過ぎます(120文字まで)。',
        ];
    }
}
