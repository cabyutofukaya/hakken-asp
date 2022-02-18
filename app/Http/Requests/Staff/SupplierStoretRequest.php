<?php

namespace App\Http\Requests\Staff;

use App\Services\SupplierService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupplierStoretRequest extends FormRequest
{
    public function __construct(SupplierService $supplierService)
    {
        $this->supplierService = $supplierService;
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $agencyId = auth("staff")->user()->agency_id;

        $days = $this->supplierService->getDateSelect();

        return [
            'code' => ['required','max:100',Rule::unique('suppliers', 'code')->where('agency_id', $agencyId)],
            'name' => 'nullable|max:120',
            'reference_date' => ['nullable',Rule::in(array_values(config("consts.suppliers.REFERENCE_DATE_LIST")))],
            'cutoff_date' => ['nullable',Rule::in(array_keys($days))],
            'payment_month' => ['nullable',Rule::in(array_values(config("consts.suppliers.PAYMENT_MONTH_LIST")))],
            'payment_day' => ['nullable',Rule::in(array_keys($days))],
            'note' => 'nullable|max:1500',
            'supplier_account_payables' => 'array',
            'supplier_account_payables.*.kinyu_code' => 'nullable|max:4',
            'supplier_account_payables.*.tenpo_code' => 'nullable|max:3',
            'supplier_account_payables.*.kinyu_name' => 'nullable|max:100',
            'supplier_account_payables.*.tenpo_name' => 'nullable|max:100',
            'supplier_account_payables.*.account_number' => 'nullable|max:16',
            'supplier_account_payables.*.account_name' => 'nullable|max:100',
        ];
    }
    
    public function messages()
    {
        return [
            'code.required' => '仕入れ先コードは必須です。',
            'code.unique' => 'すでに登録済みか、過去に使用された仕入れ先コードは登録できません。',
            'code.max' => '仕入れ先コードが長過ぎます(100文字まで)。',
            'name.max' => '仕入れ先名称が長過ぎます(120文字まで)。',
            'reference_date.in' => '基準日の指定が不正です。',
            // 'payday.in' => '支払日の指定が不正です。',
            'cutoff_date.in' => '締日の指定が不正です。',
            'payment_month.in' => '入金日(月)の指定が不正です。',
            'payment_day.in' => '入金日(日)の指定が不正です。',
            'note.max' => '備考が長過ぎます(1500文字まで)。',
            'supplier_account_payables.*.kinyu_code.max' => '振込先銀行コードが長すぎます(4桁)。',
            'supplier_account_payables.*.tenpo_code.max' => '振込先支店コードが長すぎます(3桁)。',
            'supplier_account_payables.*.kinyu_name.max' => '銀行名が長すぎます(100文字まで)。',
            'supplier_account_payables.*.tenpo_name.max' => '支店名が長すぎます(100文字まで)。',
            'supplier_account_payables.*.account_number.max' => '口座番号が長すぎます(100文字まで)。',
            'supplier_account_payables.*.account_name.max' => '口座名が長すぎます(100文字まで)。',
        ];
    }
}
