<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ExistStaff;

class AgencyDepositStoreRequest extends FormRequest
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
            'reserve_invoice_id' => $this->reserveInvoiceId,
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
            'reserve_invoice_id' => 'required',
            'amount' => 'required|integer',
            'deposit_date' => 'nullable|date',
            'record_date' => 'nullable|date',
            'manager_id' => ['nullable', new ExistStaff(auth('staff')->user()->agency->id)],
            'note' => 'nullable|max:1500',
            'list_type' => 'nullable', // APIのレスポンスに使用
            // 'reserve.updated_at' => 'nullable',
            'reserve_invoice.updated_at' => 'nullable',
        ];
    }
    
    public function messages()
    {
        return [
            'reserve_invoice_id.required' => '請求書IDは必須です。',
            'amount.required' => '入金額は必須です。',
            'amount.integer' => '入金額は半角数字で入力してください',
            'amount.integer' => '入金額は半角数字で入力してください',
            'deposit_date.date' => '入金日の入力入力形式が不正です(YYYY/MM/DD)',
            'record_date.date' => '登録日の入力入力形式が不正です(YYYY/MM/DD)',
            'note.max' => '備考が長すぎます(1500文字まで)',
        ];
    }
}
