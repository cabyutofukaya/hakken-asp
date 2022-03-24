<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;


// キャセルチャージ編集リクエスト
class ParticipantCancelChargeUpdateRequest extends FormRequest
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
            'rows.*.is_cancel' => 'nullable|boolean',
            'rows.*.cancel_charge' => 'nullable|regex:/^[0-9]+$/i', // 整数のみ許可
            'rows.*.cancel_charge_net' => 'nullable|regex:/^[0-9]+$/i', // 整数のみ許可
            'rows.*.quantity' => 'required|regex:/^[0-9]+$/i', // 整数のみ許可
            'reserve.updated_at' => 'required',
        ];
    }
    
    public function messages()
    {
        return [
            'rows.*.is_cancel.boolean' => '「キャンセル料の有無」の指定が不正です。',
            'rows.*.cancel_charge.regex' => '「キャンセル料金」の入力が正しくありません。',
            'rows.*.cancel_charge_net.regex' => '「仕入先支払料金合計」の入力が正しくありません。',
            'rows.*.quantity.required' => '数量は必須です。',
            'rows.*.quantity.regex' => '数量の入力が正しくありません。',
            'reserve.updated_at.required' => '予約情報更新日時は必須です。',
        ];
    }
}
