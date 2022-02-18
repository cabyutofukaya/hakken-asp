<?php

namespace App\Http\Requests\Staff;

use Hashids;
use App\Services\CityService;
use App\Services\SupplierService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


class SubjectAirplaneUpdateRequest extends FormRequest
{
    public function __construct(CityService $cityService, SupplierService $supplierService)
    {
        $this->cityService = $cityService;
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

    public function validationData()
    {
        return array_merge($this->request->all(), [
            'id' => $this->subjectAirplane,
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
            'id' => 'required',
            'name' => 'required|max:100',
            'booking_class' => 'nullable|max:100',
            'departure_id' => ['nullable',
                function ($attribute, $value, $fail) {
                    // $id = Hashids::decode($value)[0];
                    // if (!$this->cityService->find($id)) {
                    if (!$this->cityService->find($value)) {
                        return $fail("出発地が不正です。");
                    }
                }
            ],
            'destination_id' => ['nullable',
                function ($attribute, $value, $fail) {
                    // $id = Hashids::decode($value)[0];
                    // if (!$this->cityService->find($id)) {
                    if (!$this->cityService->find($value)) {
                        return $fail("目的地が不正です。");
                    }
                }
            ],
            'supplier_id' => ['nullable',
                function ($attribute, $value, $fail) {
                    // $id = Hashids::decode($value)[0];
                    // if (!$this->supplierService->find($id)) {
                    if (!$this->supplierService->find($value)) {
                        return $fail("仕入先が不正です。");
                    }
                }
            ],
            'ad_gross_ex' => 'nullable|integer',
            'ad_gross' => 'nullable|integer',
            'ad_cost' => 'nullable|integer',
            'ad_commission_rate' => 'nullable|integer|between:0,100', // 一応、数字の範囲を設定しておく
            'ad_net' => 'nullable|integer',
            'ad_zei_kbn' => ['nullable',Rule::in(array_values(config("consts.subject_categories.ZEI_KBN_LIST")))],
            'ad_gross_profit' => 'nullable|integer',
            'ch_gross_ex' => 'nullable|integer',
            'ch_gross' => 'nullable|integer',
            'ch_cost' => 'nullable|integer',
            'ch_commission_rate' => 'nullable|integer|between:0,100', // 一応、数字の範囲を設定しておく
            'ch_net' => 'nullable|integer',
            'ch_zei_kbn' => ['nullable',Rule::in(array_values(config("consts.subject_categories.ZEI_KBN_LIST")))],
            'ch_gross_profit' => 'nullable|integer',
            'inf_gross_ex' => 'nullable|integer',
            'inf_gross' => 'nullable|integer',
            'inf_cost' => 'nullable|integer',
            'inf_commission_rate' => 'nullable|integer|between:0,100', // 一応、数字の範囲を設定しておく
            'inf_net' => 'nullable|integer',
            'inf_zei_kbn' => ['nullable',Rule::in(array_values(config("consts.subject_categories.ZEI_KBN_LIST")))],
            'inf_gross_profit' => 'nullable|integer',
            'note' => 'nullable|max:200',
        ];
    }
    
    public function messages()
    {
        return [
            'id.required' => '航空券科目IDは必須です。',
            'name.required' => '商品名は必須です。',
            'name.max' => '商品名が長すぎます(100文字まで)。',
            'booking_class.max' => '予約クラスが長すぎます(100文字まで)。',
            'ad_gross_ex.integer' => '税抜単価(大人)は半角数字で入力してください。',
            'ad_gross.integer' => 'GROSS単価(大人)は半角数字で入力してください。',
            'ad_cost.integer' => '仕入れ値(大人)は半角数字で入力してください。',
            'ad_commission_rate.integer' => '手数料(大人)は半角数字で入力してください。',
            'ad_commission_rate.between' => '手数料(大人)は0〜100で入力してください。',
            'ad_net.integer' => 'NET単価(大人)は半角数字で入力してください。',
            'ad_zei_kbn.in' => '税区分(大人)の指定が不正です。',
            'ad_gross_profit.integer' => '粗利(大人)は半角数字で入力してください。',
            'ch_gross_ex.integer' => '税抜単価(子供)は半角数字で入力してください。',
            'ch_gross.integer' => 'GROSS単価(子供)は半角数字で入力してください。',
            'ch_cost.integer' => '仕入れ値(子供)は半角数字で入力してください。',
            'ch_commission_rate.integer' => '手数料(子供)は半角数字で入力してください。',
            'ch_commission_rate.between' => '手数料(子供)は0〜100で入力してください。',
            'ch_net.integer' => 'NET単価(子供)は半角数字で入力してください。',
            'ch_zei_kbn.in' => '税区分(子供)の指定が不正です。',
            'ch_gross_profit.integer' => '粗利(子供)は半角数字で入力してください。',
            'inf_gross_ex.integer' => '税抜単価(幼児)は半角数字で入力してください。',
            'inf_gross.integer' => 'GROSS単価(幼児)は半角数字で入力してください。',
            'inf_cost.integer' => '仕入れ値(幼児)は半角数字で入力してください。',
            'inf_commission_rate.integer' => '手数料(幼児)は半角数字で入力してください。',
            'inf_commission_rate.between' => '手数料(幼児)は0〜100で入力してください。',
            'inf_net.integer' => 'NET単価(幼児)は半角数字で入力してください。',
            'inf_zei_kbn.in' => '税区分(幼児)の指定が不正です。',
            'inf_zei_kbn.in' => '税区分(幼児)の指定が不正です。',
            'inf_gross_profit.integer' => '粗利(幼児)は半角数字で入力してください。',
            'note.max' => '備考が長すぎます(200文字まで)。',
        ];
    }
}
