<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;

class AgencyWithdrawalDeleteRequest extends FormRequest
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
            'account_payable_detail.updated_at' => 'nullable|date',
        ];
    }
    
    public function messages()
    {
        return [
            'account_payable_detail_id.updated_at.date' => '料金関連更新日時の入力入力形式が不正です(YYYY/MM/DD)',
        ];
    }
}
