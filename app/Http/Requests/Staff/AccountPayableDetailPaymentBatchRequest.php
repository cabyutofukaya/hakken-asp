<?php

namespace App\Http\Requests\Staff;

use App\Rules\ExistParticipants;
use App\Rules\ExistStaff;
use Illuminate\Foundation\Http\FormRequest;

// 支払一括処理リクエスト
class AccountPayableDetailPaymentBatchRequest extends FormRequest
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
            'data' => ['required', 'array', new ExistParticipants(auth('staff')->user()->agency->id, collect($this->data)->pluck("participant_id")->unique()->all())],
            'input.manager_id' => ['nullable', new ExistStaff(auth('staff')->user()->agency->id)],
            'input.withdrawal_date' => 'nullable|date',
            'input.record_date' => 'nullable|date',
            'input.note' => 'nullable|max:1500',
        ];
    }
    
    public function messages()
    {
        return [
            'data.required' => '仕入詳細は必須です。',
            'data.array' => '仕入詳細の指定形式が不正です。',
            'input.withdrawal_date.date' => '出金日の入力入力形式が不正です(YYYY/MM/DD)',
            'input.record_date.date' => '登録日の入力入力形式が不正です(YYYY/MM/DD)',
            'input.note.max' => '備考が長すぎます(1500文字まで)',
        ];
    }
}
