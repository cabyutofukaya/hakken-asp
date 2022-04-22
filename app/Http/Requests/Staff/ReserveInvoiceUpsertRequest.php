<?php

namespace App\Http\Requests\Staff;

use App\Rules\CheckTotalAmount;
use App\Rules\ExistBusinessUser;
use App\Rules\ExistDocumentCommon;
use App\Rules\ExistDocumentRequest;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Validator;
use Illuminate\Validation\Rule;

// 予約請求書 作成or更新リクエスト
class ReserveInvoiceUpsertRequest extends FormRequest
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
            'reserve_number' => $this->reserveNumber,
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
            'reserve_number' => 'required',
            'user_invoice_number' => 'nullable|max:100',
            'issue_date' => 'nullable|date',
            'payment_deadline' => 'nullable|date',
            'document_request_id' => ['required',new ExistDocumentRequest(auth('staff')->user()->agency->id)],
            'document_common_id' => ['nullable',new ExistDocumentCommon(auth('staff')->user()->agency->id)],
            'document_address' => 'nullable|array',
            'name' => 'nullable|max:100',
            'departure_date' => 'required|date',
            'return_date' => 'required|date|after_or_equal:departure_date',
            'manager' => 'nullable|max:32',
            'representative' => 'nullable|array',
            'participant_ids' => 'nullable|array',
            'document_common_setting' => 'nullable|array',
            'document_setting' => 'nullable|array',
            'amount_total' => ['numeric',new CheckTotalAmount($this->participant_ids, $this->option_prices, $this->airticket_prices, $this->hotel_prices)],
            'is_canceled' => 'boolean',
            'status' => ['nullable',Rule::in(array_values(config("consts.reserve_invoices.STATUS_LIST")))],
            // 代金内訳、ホテル情報等
            'option_prices' => 'nullable|array',
            'airticket_prices' => 'nullable|array',
            'hotel_prices' => 'nullable|array',
            'hotel_info' => 'nullable|array',
            'hotel_contacts' => 'nullable|array',
            'reserve.updated_at' => 'required',
        ];
    }
    
    public function messages()
    {
        return [
            'reserve_number.required' => '予約番号は必須です。',
            'user_invoice_number.max' => '請求番号が長すぎます(100文字まで)。',
            'issue_date.date' => '発行日の形式が不正です。',
            'payment_deadline.date' => '支払期限の形式が不正です。',
            'document_request_id.required' => 'テンプレートの選択は必須です。',
            'document_address.array' => '宛名(顧客情報)の形式値が不正です。',
            'name.max' => '旅行名が長すぎます(100文字まで)。',
            'departure_date.required' => '出発日は必須です。',
            'departure_date.date' => '出発日の形式が不正です。',
            'return_date.required' => '帰着日は必須です。',
            'return_date.date' => '帰着日の形式が不正です。',
            'return_date.after_or_equal' => '帰着日は出発日以降の日付を指定してください。',
            'manager.max' => '担当者名が長すぎます（32文字まで）。',
            'representative.array' => '代表者情報の入力形式が不正です。',
            'participant_ids.array' => '参加者の入力形式値が不正です。',
            'document_common_setting.array' => '共通設定の入力形式が不正です。',
            'document_setting.array' => '各種表示設定の入力値が不正です。',
            'amount_total.numeric' => '合計金額の入力が不正です。',
            'is_canceled.boolean' => 'キャンセルフラグの指定が不正です。',
            'status.in' => 'ステータスの入力値が不正です。',
            'option_prices.array' => 'オプション科目の入力形式値が不正です。',
            'airticket_prices.array' => '航空券科目の入力形式値が不正です。',
            'hotel_prices.array' => 'ホテル科目の入力形式値が不正です。',
            'hotel_info.array' => '宿泊施設情報の入力形式値が不正です。',
            'hotel_contacts.array' => '宿泊施設連絡先の入力形式値が不正です。',
            'reserve.updated_at.required' => '予約情報更新日時は必須です。',
        ];
    }
}
