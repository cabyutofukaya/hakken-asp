<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Services\CountryService;

class UserVisaUpdateRequest extends FormRequest
{
    public function __construct(
        CountryService $countryService
        )
    {
        $this->countryService = $countryService;
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
            'id' => $this->userVisa,
            'user_number' => $this->userNumber,
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
            'user_number' => 'required',
            'number' => 'nullable|max:100',
            'country_code' => ['nullable',
                function ($attribute, $value, $fail) {
                    if(!in_array($value, array_keys($this->countryService->getCodeNameList()))){
                        return $fail("国の指定が不正です。");
                    }
                }
            ],
            'kind' => 'nullable|max:100',
            'issue_place_code' => ['nullable',
                function ($attribute, $value, $fail) {
                    if(!in_array($value, array_keys($this->countryService->getCodeNameList()))){
                        return $fail("発行地の指定が不正です。");
                    }
                }
            ],
            'issue_date' => 'nullable|date',
            'expiration_date' => 'nullable|date',
            'note' => 'nullable|max:1000',
        ];
    }
    
    public function messages()
    {
        return [
            'id.required' => 'ビザ情報IDは必須です。',
            'user_number.required' => '顧客番号は必須です。',
            'number.max' => '番号(ビザ情報)が長すぎます(100文字まで)。',
            'kind.max' => '種別(ビザ情報)が長すぎます(100文字まで)。',
            'issue_date.date' => '発行日(ビザ情報)の入力形式が正しくありません(YYYY-MM-DD)。',
            'expiration_date.date' => '有効期限(ビザ情報)の入力形式が正しくありません(YYYY-MM-DD)。',
            'note.max' => '備考(ビザ情報)が長すぎます(1000文字まで)。',
        ];
    }
}
