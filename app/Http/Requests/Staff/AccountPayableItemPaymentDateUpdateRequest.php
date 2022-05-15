<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class AccountPayableItemPaymentDateUpdateRequest extends FormRequest
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
            'account_payable_item_id' => $this->accountPayableItemId,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        // 現状、payment_dateの変更にしか使っていないが他のフィールド更新に使う場合は適宜追加
        return [
            'account_payable_item_id' => 'required',
            'payment_date' => 'nullable|date',
            'updated_at' => 'nullable',
        ];
    }
    
    public function messages()
    {
        return [
            'account_payable_item_id.required' => '仕入詳細IDは必須です。',
            'payment_date.date' => '支払日の入力入力形式が不正です(YYYY/MM/DD)。',
        ];
    }
}
