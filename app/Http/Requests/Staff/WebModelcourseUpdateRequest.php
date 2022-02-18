<?php

namespace App\Http\Requests\Staff;

use App\Rules\ExistArea;
use App\Rules\ExistStaff;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WebModelcourseUpdateRequest extends FormRequest
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
            'course_no' => $this->courseNo,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $agencyId = auth('staff')->user()->agency->id;

        return [
            'course_no' => 'required',
            'name' => 'nullable|max:32',
            'description' => 'nullable|max:3000',
            'stays' => ['required',Rule::in(array_values(config("consts.web_modelcourses.STAY_LIST")))],
            'price_per_ad' => 'nullable|max:32',
            'price_per_ch' => 'nullable|max:32',
            'price_per_inf' => 'nullable|max:32',
            'departure_id' => ['nullable',new ExistArea($agencyId)],
            'departure_place' => 'nullable|max:100',
            'destination_id' => ['nullable',new ExistArea($agencyId)],
            'destination_place' => 'nullable|max:100',
            'author_id' => ['nullable', new ExistStaff($agencyId)],
            // タグ
            'web_modelcourse_tags.tag' => 'nullable|array',
            // 写真
            'web_modelcourse_photo' => 'nullable',
            'upload_web_modelcourse_photo' => 'nullable',
        ];
    }
    
    public function messages()
    {
        return [
            'course_no.required' => 'コース番号は必須です。',
            'name.max' => 'コース名が長すぎます(32文字まで)。',
            'description.max' => '説明文が長すぎます(3000文字まで)。',
            'stays.required' => '日数は必須です。',
            'stays.in' => '日数の指定が不正です。',
            'price_per_ad.max' => '大人料金が長すぎます(32文字まで)。',
            'price_per_ch.max' => '子供料金が長すぎます(32文字まで)。',
            'price_per_inf.max' => '幼児料金が長すぎます(32文字まで)。',
            'departure_place.max' => '住所・名称が長すぎます(100文字まで)。',
            'destination_place.max' => '住所・名称が長すぎます(100文字まで)。',
            'web_modelcourse_tags.tag.array' => 'タグの送信形式が不正です。'
        ];
    }
}
