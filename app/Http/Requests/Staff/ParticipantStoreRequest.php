<?php

namespace App\Http\Requests\Staff;

use App\Services\CountryService;
use App\Services\UserService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ParticipantStoreRequest extends FormRequest
{
    public function __construct(
        CountryService $countryService,
        UserService $userService
    ) {
        $this->countryService = $countryService;
        $this->userService = $userService;
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

    public function validationData()
    {
        return array_merge($this->request->all(), [
            'application_step' => $this->applicationStep,
            'control_number' => $this->controlNumber,
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
            'control_number' => 'required',
            'application_step' => 'required',
            'ad_number' => 'required|numeric|min:0',
            'ch_number' => 'required|numeric|min:0',
            'inf_number' => ["required","numeric","min:0",function ($attribute, $value, $fail) {
                if (($this->ad_number + $this->ch_number + $this->inf_number) > config('consts.const.PARTICIPANT_MAX_NUM')) {
                    $fail("参加者人数が多すぎます(".config('consts.const.PARTICIPANT_MAX_NUM')."名以下で設定してください)");
                }
            }],
        ];
    }
    
    public function messages()
    {
        return [
            'control_number.required' => '予約/見積番号は必須です。',
            'application_step.required' => '申込状態は必須です。',
            'ad_number.required' => '大人人数は必須です。',
            'ad_number.numeric' => '大人人数は半角数字で入力してください。',
            'ad_number.min' => '大人人数は0以上の数字で入力してください。',
            'ad_number.max' => '大人人数は1000以下の数字で入力してください。',
            'ch_number.required' => '子供人数は必須です。',
            'ch_number.numeric' => '子供人数は半角数字で入力してください。',
            'ch_number.min' => '子供人数は0以上の数字で入力してください。',
            'ch_number.max' => '子供人数は1000以下の数字で入力してください。',
            'inf_number.required' => '幼児人数は必須です。',
            'inf_number.numeric' => '幼児人数は半角数字で入力してください。',
            'inf_number.min' => '幼児人数は0以上の数字で入力してください。',
            'inf_number.max' => '幼児人数は1000以下の数字で入力してください。',
        ];
    }
}
