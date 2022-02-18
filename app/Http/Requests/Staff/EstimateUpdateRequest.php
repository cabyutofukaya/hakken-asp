<?php

namespace App\Http\Requests\Staff;

use App\Rules\ExistArea;
use App\Rules\ExistApplicantCustomer;
use App\Rules\ExistStaff;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EstimateUpdateRequest extends FormRequest
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
            'estimate_number' => $this->estimateNumber,
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
            'estimate_number' => 'required',
            'participant_type' => ['required',Rule::in(array_values(config("consts.reserves.PARTICIPANT_TYPE_LIST")))],
            'applicant_user_number' => ['required',new ExistApplicantCustomer(auth('staff')->user()->agency->id, $this->participant_type)],
            'name' => 'nullable|max:100',
            'departure_date' => 'nullable|date',
            'return_date' => 'nullable|date|after_or_equal:departure_date',
            'departure_id' => ['nullable',new ExistArea(auth('staff')->user()->agency->id)],
            'departure_place' => 'nullable|max:100',
            'destination_id' => ['nullable',new ExistArea(auth('staff')->user()->agency->id)],
            'destination_place' => 'nullable|max:100',
            'note' => 'nullable|max:100',
            'manager_id' => ['nullable', new ExistStaff(auth('staff')->user()->agency->id)],
            'updated_at' => 'nullable',
        ];
    }
    
    public function messages()
    {
        return [
            'estimate_number.required' => '見積番号は必須です。',
            'participant_type.required' => '顧客種別は必須です。',
            'applicant_user_number.required' => '顧客が選択されていません。',
            'name.max' => '案件名が長すぎます(100文字まで)。',
            'departure_date.date' => '出発日の入力形式が不正です(YYYY-MM-DD)。',
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
