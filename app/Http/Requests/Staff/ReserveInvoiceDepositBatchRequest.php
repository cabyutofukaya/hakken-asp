<?php

namespace App\Http\Requests\Staff;

use App\Rules\ExistStaff;
use Illuminate\Foundation\Http\FormRequest;

class ReserveInvoiceDepositBatchRequest extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'reserve_bundle_invoice_id' => 'nullable', // 一括請求内訳ページからのAPI実行時に渡ってくるパラメータ
            'data' => 'required|array',
            'input.manager_id' => ['nullable', new ExistStaff(auth('staff')->user()->agency->id)],
            'input.deposit_date' => 'nullable|date',
            'input.record_date' => 'nullable|date',
            'input.note' => 'nullable|max:1500',
            'params' => 'nullable|array',
        ];

    }
    
    public function messages()
    {
        return [
            'data.required' => '請求情報は必須です。',
            'data.array' => '請求情報の指定形式が不正です。',
            'input.deposit_date.date' => '出金日の入力入力形式が不正です(YYYY/MM/DD)',
            'input.record_date.date' => '登録日の入力入力形式が不正です(YYYY/MM/DD)',
            'input.note.max' => '備考が長すぎます(1500文字まで)',
            'params.array' => '検索パラメータの形式が不正です。',
        ];

    }
}
