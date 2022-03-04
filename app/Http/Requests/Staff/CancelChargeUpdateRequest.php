<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


// キャセルチャージ編集リクエスト
class CancelChargeUpdateRequest extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'reserve_number' => 'required',
            'rows.*.is_cancel' => 'nullable|boolean',
            'rows.*.cancel_charge' => 'nullable|regex:/^[0-9]+$/i', // 整数のみ許可
            'rows.*.cancel_charge_net' => 'nullable|regex:/^[0-9]+$/i', // 整数のみ許可
            'rows.*.quantity' => 'required|regex:/^[0-9]+$/i', // 整数のみ許可
        ];
    }
    
    public function messages()
    {
        return [
            'reserve_number.required' => '予約番号は必須です。',
            'rows.*.is_cancel.boolean' => '「キャンセル料の有無」の指定が不正です。',
            'rows.*.cancel_charge.regex' => '「キャンセル料金」の入力が正しくありません。',
            'rows.*.cancel_charge_net.regex' => '「仕入れ先支払料金」の入力が正しくありません。',
            'rows.*.quantity.required' => '数量は必須です。',
            'rows.*.quantity.regex' => '数量の入力が正しくありません。',
        ];
    }
}
