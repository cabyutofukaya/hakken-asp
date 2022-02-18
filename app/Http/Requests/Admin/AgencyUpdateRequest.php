<?php

namespace App\Http\Requests\Admin;

use Auth;
use App\Rules\CheckPassword;
use App\Models\Agency;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AgencyUpdateRequest extends FormRequest
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
            'id' => $this->agency,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $id = $this->route('agency');
        return [
            'id' => 'required',
            'registration_at' => 'required|date',
            'master_staff.password' => ['nullable', 'string', new CheckPassword()],
            'company_name' => 'required',
            'company_kana' => 'required|kana',
            'representative_name' => 'nullable|max:32',
            'representative_kana' => 'nullable|max:32|kana',
            'person_in_charge_name' => 'required|max:32',
            'person_in_charge_kana' => 'nullable|max:32|kana',
            'zip_code' => 'required|regex:/^[0-9]{7}$/',
            'prefecture_code' => 'required|regex:/^[0-9]{2}$/',
            'address1' => 'required|max:100',
            'address2' => 'nullable|max:100',
            'capital' => 'nullable',
            'email' => ['required','email'],
            'tel' => 'nullable|regex:/^[0-9\-\(\)]+$/',
            'fax' => 'nullable|regex:/^[0-9\-\(\)]+$/',
            'emergency_contact' => 'nullable',
            'establishment_at' => 'nullable|date',
            'travel_agency_registration_at' => 'nullable|date',
            'business_scope' => ['required',Rule::in(array_values(config("consts.agencies.BUSINESS_SCOPE_LIST")))],
            'employees_number' => 'nullable|natural_number',
            'registered_administrative_agency' => 'nullable',
            'registration_type' => ['required',Rule::in(array_values(config("consts.agencies.REGISTRATION_TYPE_LIST")))],
            'registration_number' => 'nullable',
            'travel_agency_association' => ['required',Rule::in(array_values(config("consts.agencies.TRAVEL_AGENCY_ASSOCIATION_LIST")))],
            'fair_trade_council' => 'required|boolean',
            'iata' => 'required|boolean',
            'etbt' => 'required|boolean',
            'bond_guarantee' => 'required|boolean',
            'number_staff_allowed' => 'required|integer|between:1,' . config('consts.const.NUMBER_STAFF_ALLOWED_MAX'),
            'status' => ['required',Rule::in(array_values(config("consts.agencies.STATUS_LIST")))],
            'registration_at' => 'required|date',
            'manager' => 'nullable|max:16',
            'upload_agreement_file' => 'nullable',
            'upload_terms_file' => 'nullable',
            // 契約関連
            'trial' => 'required|boolean',
            'trial_start_at' => 'required_if:trial,1|nullable|date',
            'trial_end_at' => 'required_if:trial,1|nullable|date',
            'contracts.*.contract_plan_id' => 'nullable|exists:contract_plans,id,deleted_at,NULL',
            'contracts.*.start_at' => 'nullable|date',
            'contracts.*.end_at' => 'nullable|date',
            'updated_at' => 'required',
        ];
    }
    
    public function messages()
    {
        return [
            'id.required' => '会社IDは必須です',
            'registration_at.required' => '登録年月日は必須です',
            'registration_at.date' => '登録年月日の形式が不正です',
            'master_staff.password.string' => 'パスワードは文字列で入力してください',
            'company_name.required' => '会社名は必須です',
            'company_kana.required' => '会社名（カナ）は必須です',
            'company_kana.kana' => '会社名（カナ）は全角カタカナで入力してください',
            'representative_name.max' => '代表者名が長過ぎます(32文字まで)',
            'representative_kana.max' => '代表者名（カナ）が長過ぎます(32文字まで)',
            'representative_kana.kana' => '代表者名（カナ）は全角カタカナで入力してください',
            'person_in_charge_name.required' => '担当者名は必須です',
            'person_in_charge_name.max' => '担当者名が長過ぎます(32文字まで)',
            'person_in_charge_kana.max' => '担当者名（カナ）が長過ぎます(32文字まで)',
            'person_in_charge_kana.kana' => '担当者名（カナ）は全角カタカナで入力してください',
            'zip_code.required' => '郵便番号は必須です',
            'zip_code.regex' => '郵便番号は半角数字7桁で入力してください',
            'prefecture_code.required' => '都道府県は必須です',
            'prefecture_code.regex' => '都道府県の入力形式が間違っています',
            'address1.required' => '住所1は必須です',
            'email.required' => 'メールアドレスは必須です',
            'email.email' => 'メールアドレスの形式が不正です',
            'tel.regex' => '電話番号の入力形式が間違っています',
            'fax.regex' => 'FAX番号の入力形式が間違っています',
            'establishment_at.date' => '設立年月日の入力形式が不正です',
            'travel_agency_registration_at.date' => '旅行業登録年月日の入力形式が不正です',
            'business_scope.required' => '業務範囲は必須です',
            'business_scope.in' => '業務範囲の指定が不正です',
            'employees_number.natural_number' => '従業員数は自然数で入力してください（半角数字）',
            'registration_type.required' => '登録種別は必須です',
            'registration_type.in' => '登録種別の値が不正です',
            'registration_type.required' => '登録種別は必須です',
            'travel_agency_association.required' => '旅行業協会は必須です',
            'travel_agency_association.in' => '旅行業協会の値が不正です',
            'fair_trade_council.required' => '旅公取協は必須です',
            'fair_trade_council.boolean' => '旅公取協の値が不正です',
            'iata.required' => 'IATA加入は必須です',
            'iata.boolean' => 'IATA加入の値が不正です',
            'etbt.required' => 'e-TBT加入は必須です',
            'etbt.boolean' => 'e-TBT加入の値が不正です',
            'bond_guarantee.required' => 'ボンド保証制度は必須です',
            'bond_guarantee.boolean' => 'ボンド保証制度の値が不正です',
            // 'number_staff_allowed.required' => 'スタッフ登録許可数は必須です',
            // 'status.required' => '状態は必須です',
            'registration_at.required' => '登録年月日は必須です',
            'registration_at.date' => '登録年月日の入力形式が不正です',
            'manager.max' => '自社担当が長すぎます。',
            // 契約関連
            'trial.required' => 'トライアルの有無は必須です。',
            'trial.boolean' => 'トライアルの有無の値が不正です。',
            'trial_start_at.required_if' => 'トライアル開始日は必須です。',
            'trial_start_at.date' => 'トライアル開始日のフォーマットが不正です。',
            'trial_end_at.required_if' => 'トライアル終了日は必須です。',
            'trial_end_at.date' => 'トライアル終了日のフォーマットが不正です。',
            'contracts.*.contract_plan_id.exists' => 'プランIDの値が不正です。',
            'contracts.*.start_at.date' => '契約開始日のフォーマットが不正です。',
            'contracts.*.end_at.date' => '契約終了月のフォーマットが不正です。',
            //
            'updated_at.required' => '更新日は必須です。',
        ];
    }
}
