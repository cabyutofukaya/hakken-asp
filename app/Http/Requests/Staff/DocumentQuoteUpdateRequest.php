<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DocumentQuoteUpdateRequest extends FormRequest
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
            'id' => $this->documentQuote,
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
            'management_name' => 'nullable|max:32',
            'description' => 'nullable|max:100',
            'title' => 'required|max:100',
            'setting' => 'nullable|array',
            'document_common_id' => [
                'nullable', 
                Rule::exists('document_commons', 'id')->where(function ($query) {
                    $query->where('agency_id', auth('staff')->user()->agency_id)->where('id', request()->input('document_common_id'));
            })],
            'seal' => 'nullable|boolean',
            'seal_number' => 'nullable|integer',
            'seal_items' => 'nullable|array',
            'seal_wording' => 'nullable|max:100',
            'information' => 'nullable|max:1000',
            'note' => 'nullable|max:1000',
        ];
    }
    
    public function messages()
    {
        return [
            'id.required' => 'IDは必須です。',
            'title.required' => '表題は必須です。',
            'management_name.max' => '管理名称が長すぎます(32文字まで)。',
            'name.required' => 'テンプレート名は必須です。',
            'setting.array' => '設定項目の入力形式が不正です。',
            'document_common_id.exists' => '宛名/自社情報共通設定の値が不正です。',
            'name.max' => 'テンプレート名が長過ぎます(100文字まで)。',
            'description.max' => '説明文が長過ぎます(100文字まで)。',
            'seal.boolean' => '検印の値が不正です。',
            'seal_number.integer' => '検印欄表示数の値が不正です。',
            'seal_wording.max' => '枠下文言が長過ぎます(100文字まで)。',
            'information.max' => '案内文が長過ぎます(1000文字まで)。',
            'note.max' => '備考が長過ぎます(1000文字まで)。',
        ];
    }
}
