<?php

namespace App\Http\Requests\Staff;

use App\Rules\CheckBundleTotalAmount;
use App\Rules\ExistBusinessUser;
use App\Rules\ExistDocumentCommon;
use App\Rules\ExistDocumentRequestAll;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Validator;
use Illuminate\Validation\Rule;


// 一括請求 更新リクエスト
class ReserveBundleInvoiceUpdateRequest extends FormRequest
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
            'reserve_bundle_invoice_id' => $this->reserveBundleInvoiceId,
        ]);
    }

    public function withValidator(Validator $validator)
    {
        // 申し込み顧客種別が法人の場合は法人顧客IDが必須
        $validator->sometimes('business_user_id', ['required', new ExistBusinessUser(auth('staff')->user()->agency->id)], function () {
            return Arr::get($this->document_address, 'type') === config('consts.reserves.PARTICIPANT_TYPE_BUSINESS');
        });
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'reserve_bundle_invoice_id' => 'required',
            'business_user_id' => ['required',new ExistBusinessUser(auth('staff')->user()->agency->id)],
            'user_bundle_invoice_number' => 'nullable|max:100',
            'issue_date' => 'nullable|date',
            'payment_deadline' => 'nullable|date',
            'document_request_all_id' => ['required',new ExistDocumentRequestAll(auth('staff')->user()->agency->id)],
            'document_common_id' => ['nullable',new ExistDocumentCommon(auth('staff')->user()->agency->id)],
            'document_address' => 'nullable|array',
            'name' => 'nullable|max:100',
            'period_from' => 'nullable|date',
            'period_to' => 'nullable|date|after_or_equal:departure_date',
            'manager' => 'nullable|max:32', // ??
            'partner_manager_ids' => 'nullable|array',
            'document_common_setting' => 'nullable|array',
            'document_setting' => 'nullable|array',
            'amount_total' => ['numeric',new CheckBundleTotalAmount($this->partner_manager_ids, $this->reserve_prices, $this->reserve_cancel_info)],
            'status' => ['nullable',Rule::in(array_values(config("consts.reserve_bundle_invoices.STATUS_LIST")))],
            'updated_at' => 'nullable|date',
            // 代金内訳情報等
            'reserve_prices' => 'required|array',
            'reserve_cancel_info' => 'required|array', // キャンセル予約情報。金額の検算に使用
        ];
    }
    
    public function messages()
    {
        return [
            'reserve_bundle_invoice_id.required' => '請求書IDは必須です。',
            'business_user_id.required' => '会社IDは必須です。',
            'user_bundle_invoice_number.max' => '請求番号が長すぎます(100文字まで)。',
            'issue_date.date' => '発行日の形式が不正です。',
            'payment_deadline.date' => '支払期限の形式が不正です。',
            'document_request_all_id.required' => 'テンプレートの選択は必須です。',
            'document_address.array' => '宛名(顧客情報)の形式値が不正です。',
            'name.max' => '旅行名が長すぎます(100文字まで)。',
            'period_from.required' => '期間(開始)は必須です。',
            'period_from.date' => '期間(開始)の形式が不正です。',
            'period_to.required' => '期間(終了)は必須です。',
            'period_to.date' => '期間(終了)の形式が不正です。',
            'period_to.after_or_equal' => '期間(終了)は期間(開始)以降の日付を指定してください。',
            'manager.max' => '担当者名が長すぎます（32文字まで）。',
            'partner_manager_ids.array' => '担当者の入力形式値が不正です。',
            'document_common_setting.array' => '共通設定の入力形式が不正です。',
            'document_setting.array' => '各種表示設定の入力値が不正です。',
            'amount_total.numeric' => '合計金額の入力が不正です。',
            'status.in' => 'ステータスの入力値が不正です。',
            'reserve_prices.required' => '料金内訳データは必須です。',
            'reserve_prices.array' => '料金内訳の入力形式値が不正です。',
            'reserve_cancel_info.required' => 'キャンセル予約情報は必須です。',
            'reserve_cancel_info.array' => 'キャンセル予約情報の入力形式値が不正です。',
        ];
    }
}
