<?php

namespace App\Http\Requests\Staff;

use App\Rules\ExistStaff;
use App\Services\ReserveInvoiceService;
use Illuminate\Foundation\Http\FormRequest;

class AgencyBundleDepositStoreRequest extends FormRequest
{
    public function __construct(ReserveInvoiceService $reserveInvoiceService)
    {
        $this->reserveInvoiceService = $reserveInvoiceService;
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
            'reserve_bundle_invoice_id' => $this->reserveBundleInvoiceId,
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
            'reserve_bundle_invoice_id' => 'required',
            'amount' => ['required','integer',function ($attribute, $value, $fail) { // 入力金額と子請求書の未入金額合計がイコールかチェック
                $reserveInvoices = $this->reserveInvoiceService->getByReserveBundleInvoiceId($this->agencyAccount, $this->reserve_bundle_invoice_id, ['agency_deposits'], ['id','amount_total'], false);
                if ($reserveInvoices->sum('sum_not_deposit') != $value) {
                    return $fail("入金額と各請求書の合計額が正しくありません。\n各請求書が正しい内容で保存されているかご確認ください。");
                }
            }],
            'deposit_date' => 'nullable|date',
            'record_date' => 'nullable|date',
            'manager_id' => ['nullable', new ExistStaff(auth('staff')->user()->agency->id)],
            'note' => 'nullable|max:1500',
            'reserve_bundle_invoice.updated_at' => 'nullable',
        ];
    }
    
    public function messages()
    {
        return [
            'reserve_bundle_invoice_id.required' => '請求書IDは必須です。',
            'amount.required' => '入金額は必須です。',
            'amount.integer' => '入金額は半角数字で入力してください',
            'amount.integer' => '入金額は半角数字で入力してください',
            'deposit_date.date' => '入金日の入力入力形式が不正です(YYYY/MM/DD)',
            'record_date.date' => '登録日の入力入力形式が不正です(YYYY/MM/DD)',
            'note.max' => '備考が長すぎます(1500文字まで)',
        ];
    }
}
