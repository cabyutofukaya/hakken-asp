<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Rules\CheckOnlineConsultDate;


class WebOnlineScheduleUpdateRequest extends FormRequest
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
            'web_reserve_ext_id' => 'required',
            'reserve_id' => 'required',
            'consult_date' => ['required','date',new CheckOnlineConsultDate],
            'hour' => 'required|regex:/^[0-9]{2}$/',
            'minute' => 'required|regex:/^[0-9]{2}$/',
        ];
    }
    
    public function messages()
    {
        return [
            'web_reserve_ext_id.required' => 'Web予約IDは必須です。',
            'reserve_id.required' => '予約IDは必須です。',
            'consult_date.required' => '相談日は必須です。',
            'consult_date.date' => '相談日の入力形式が不正です。',
            'hour.required' => '時間は必須です。',
            'hour.regex' => '時間の入力形式が不正です。',
            'minute.required' => '分は必須です。',
            'minute.regex' => '分の入力形式が不正です。',
        ];
    }
}
