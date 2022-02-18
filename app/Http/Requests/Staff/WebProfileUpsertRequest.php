<?php

namespace App\Http\Requests\Staff;

use App\Services\WebProfileService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WebProfileUpsertRequest extends FormRequest
{
    public function __construct(
        WebProfileService $webProfileService
        )
    {
        $this->webProfileService = $webProfileService;
    }

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
            'staff.web_valid' => 'required|boolean',
            'post' => 'nullable|max:100',
            'name' => 'nullable|max:32',
            'name_kana' => 'nullable|max:32',
            'name' => 'nullable|max:32',
            'name_roman' => 'nullable|max:100',
            'email' => 'nullable|max:255',
            'tel' => 'nullable|max:32',
            'sex' => ['nullable',Rule::in(array_values(config("consts.web_profiles.SEX_LIST")))],
            'birthday_y' => ['nullable',
                function ($attribute, $value, $fail) {
                    if(!in_array($value, array_keys($this->webProfileService->getBirthDayYearSelect()))){
                        return $fail("生年月日(年)の指定が不正です。");
                    }
                }
            ],
            'birthday_m' => ['nullable',
                function ($attribute, $value, $fail) {
                    if(!in_array($value, array_keys($this->webProfileService->getBirthDayMonthSelect()))){
                        return $fail("生年月日(月)の指定が不正です。");
                    }
                }
            ],
            'birthday_d' => ['nullable',
                function ($attribute, $value, $fail) {
                    if(!in_array($value, array_keys($this->webProfileService->getBirthDayDaySelect()))){
                        return $fail("生年月日(日)の指定が不正です。");
                    }
                }
            ],
            'introduction' => 'nullable|max:3000',
            'business_area' => 'nullable|array',
            'purpose' => 'nullable|array',
            'interest' => 'nullable|array',
            'web_profile_tags.tag' => 'nullable|array',
            // 写真
            'web_profile_profile_photo' => 'nullable',
            'web_profile_cover_photo' => 'nullable',
            'upload_web_profile_profile_photo' => 'nullable',
            'upload_web_profile_cover_photo' => 'nullable',
        ];
    }
    
    public function messages()
    {
        return [
            'staff.web_valid.required' => '「HAKKEN WEBの有効化」選択は必須です。',
            'staff.web_valid.boolean' => '「HAKKEN WEBの有効化」の選択値が不正です。',
            'post.max' => '役職・名称が長すぎます(100文字まで)。',
            'name.max' => '氏名が長すぎます(32文字まで)。',
            'name_kana.max' => '氏名(カナ)が長すぎます(32文字まで)。',
            'name_roman.max' => '氏名(ローマ字)が長すぎます(100文字まで)。',
            'email.max' => 'メールアドレスが長すぎます(255文字まで)。',
            'tel.max' => '電話番号が長すぎます(32文字まで)。',
            'sex.in' => '性別の指定が不正です。',
            'introduction.max' => '自己紹介文が長すぎます(3000文字まで)。',
            'business_area.array' => '提案可能エリアの形式が不正です。',
            'purpose.array' => '得意な旅行分野の形式が不正です。',
            'interest.array' => '得意な旅行内容の形式が不正です。',
            'web_profile_tags.tag.array' => 'タグの送信形式が不正です。'
        ];
    }
}
