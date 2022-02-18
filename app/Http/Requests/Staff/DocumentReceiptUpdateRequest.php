<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DocumentReceiptUpdateRequest extends FormRequest
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
            'id' => $this->documentReceipt,
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
            // 'name' => 'required|max:100',
            // 'description' => 'nullable|max:100',
            'title' => 'required|max:100',
            'document_common_id' => [
                'nullable', 
                Rule::exists('document_commons', 'id')->where(function ($query) {
                    $query->where('agency_id', auth('staff')->user()->agency_id)->where('id', request()->input('document_common_id'));
            })],
            'proviso' => 'nullable|max:1000',
            'note' => 'nullable|max:1000',
        ];
    }
    
    public function messages()
    {
        return [
            'id.required' => 'IDは必須です。',
            'title.required' => '表題は必須です。',
            // 'name.required' => 'テンプレート名は必須です。',
            // 'name.max' => 'テンプレート名が長過ぎます(100文字まで)。',
            // 'description.max' => '説明文が長過ぎます(100文字まで)。',
            'document_common_id.exists' => '宛名/自社情報共通設定の値が不正です。',
            'proviso.max' => '但し書きが長過ぎます(1000文字まで)。',
            'note.max' => '備考が長過ぎます(1000文字まで)。',
        ];
    }
}
