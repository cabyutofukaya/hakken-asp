<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\ExistStaff;
use App\Rules\ExistParticipant;

class AgencyWithdrawalStoreRequest extends FormRequest
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
            'account_payable_detail_id' => $this->accountPayableDetailId,
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
            'account_payable_detail_id' => 'required',
            'amount' => 'required|integer',
            'withdrawal_date' => 'nullable|date',
            'record_date' => 'nullable|date',
            'participant_id' => ['required', new ExistParticipant(auth('staff')->user()->agency->id)],
            'manager_id' => ['nullable', new ExistStaff(auth('staff')->user()->agency->id)],
            'note' => 'nullable|max:1500',
            'supplier_id_log' => 'required',
            'account_payable_detail.updated_at' => 'nullable',
        ];
    }
    
    public function messages()
    {
        return [
            'account_payable_detail_id.required' => '仕入詳細IDは必須です。',
            'amount.required' => '出金額は必須です。',
            'amount.integer' => '出金額は半角数字で入力してください',
            'amount.integer' => '出金額は半角数字で入力してください',
            'withdrawal_date.date' => '出金日の入力入力形式が不正です(YYYY/MM/DD)',
            'record_date.date' => '登録日の入力入力形式が不正です(YYYY/MM/DD)',
            'participant_id.required' => '参加者IDは必須です。',
            'supplier_id_log.required' => '仕入先IDは必須です。',
            'note.max' => '備考が長すぎます(1500文字まで)',
        ];
    }
}
