<?php

namespace App\Http\Requests\Staff;

use App\Rules\ExistArea;
use App\Rules\ExistApplicantCustomer;
use App\Rules\ExistStaff;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EstimateStoretRequest extends FormRequest
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
            'participant_type' => ['required',Rule::in(array_values(config("consts.reserves.PARTICIPANT_TYPE_LIST")))],
            'applicant_user_number' => ['required',new ExistApplicantCustomer(auth('staff')->user()->agency->id, $this->participant_type)],
            'name' => 'nullable|max:100',
            'departure_date' => ['nullable',Rule::requiredIf(function () {
                return $this->return_date;
            }),'date'],
            'return_date' => ['nullable',Rule::requiredIf(function () {
                return $this->departure_date;
            }),'date','after_or_equal:departure_date'],
            'departure_id' => ['nullable',new ExistArea(auth('staff')->user()->agency->id)],
            'departure_place' => 'nullable|max:100',
            'destination_id' => ['nullable',new ExistArea(auth('staff')->user()->agency->id)],
            'destination_place' => 'nullable|max:100',
            'note' => 'nullable|max:3000',
            'manager_id' => ['nullable', new ExistStaff(auth('staff')->user()->agency->id)],
        ];
    }
    
    public function messages()
    {
        return [
            'participant_type.required' => '顧客種別は必須です。',
            'applicant_user_number.required' => '顧客が選択されていません。',
            'name.max' => '旅行名が長すぎます(100文字まで)。',
            'departure_date.required' => '出発日を入力してください。',
            'departure_date.date' => '出発日の入力形式が不正です(YYYY-MM-DD)。',
            'return_date.required' => '帰着日を入力してください。',
            'return_date.date' => '帰着日の入力形式が不正です(YYYY-MM-DD)。',
            'return_date.after_or_equal' => '帰着日は出発日以降の日付を指定してください。',
            'departure_place.max' => '住所・名称が長すぎます(100文字まで)。',
            'destination.max' => '住所・名称が長すぎます(100文字まで)。',
            'note.max' => '備考が長すぎます(3000文字まで)。',
        ];
    }

    public function attributes()
    {
        return [
            'departure_id' => '出発地',
            'destination_id' => '目的地',
        ];
    }

}
