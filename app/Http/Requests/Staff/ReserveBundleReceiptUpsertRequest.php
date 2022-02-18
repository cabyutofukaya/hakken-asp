<?php

namespace App\Http\Requests\Staff;

use App\Rules\CheckTotalAmount;
use App\Rules\ExistBusinessUser;
use App\Rules\ExistDocumentCommon;
use App\Rules\ExistDocumentReceipt;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Validator;
use Illuminate\Validation\Rule;


// 一括領収書 作成or更新リクエスト
class ReserveBundleReceiptUpsertRequest extends FormRequest
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
            'reserve_bundle_invoice_hash_id' => $this->reserveBundleInvoiceHashId,
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
            'reserve_bundle_invoice_hash_id' => 'required',
            'user_receipt_number' => 'nullable|max:100',
            'issue_date' => 'nullable|date',
            'document_receipt_id' => ['required',new ExistDocumentReceipt(auth('staff')->user()->agency->id)],
            'document_common_id' => ['nullable',new ExistDocumentCommon(auth('staff')->user()->agency->id)],
            'document_address' => 'nullable|array',
            'manager' => 'nullable|max:32',
            'document_common_setting' => 'nullable|array',
            'document_setting' => 'nullable|array',
            'receipt_amount' => 'numeric',
            'status' => ['nullable',Rule::in(array_values(config("consts.reserve_bundle_receipts.STATUS_LIST")))],
            'updated_at' => 'nullable',
        ];
    }
    
    public function messages()
    {
        return [
            'reserve_bundle_invoice_hash_id.required' => '書類識別IDは必須です。',
            'business_user_id.required' => '法人顧客IDは必須です。',
            'user_receipt_number.max' => '請求番号が長すぎます(100文字まで)。',
            'issue_date.date' => '発行日の形式が不正です。',
            'document_receipt_id.required' => 'テンプレートの選択は必須です。',
            'document_address.array' => '宛名(顧客情報)の形式値が不正です。',
            'manager.max' => '担当者名が長すぎます（32文字まで）。',
            'document_common_setting.array' => '共通設定の入力形式が不正です。',
            'document_setting.array' => '各種表示設定の入力値が不正です。',
            'receipt_amount.numeric' => '合計金額の入力が不正です。',
            'status.in' => 'ステータスの入力値が不正です。',
        ];
    }
}
