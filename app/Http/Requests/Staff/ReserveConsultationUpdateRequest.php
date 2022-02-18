<?php

namespace App\Http\Requests\Staff;

use App\Rules\ExistStaff;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReserveConsultationUpdateRequest extends FormRequest
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
            'control_number' => $this->controlNumber,
            'consul_number' => $this->consulNumber,
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
            'control_number' => 'nullable',
            'consul_number' => 'required',
            'title' => 'nullable|max:32',
            'manager_id' => ['nullable', new ExistStaff(auth('staff')->user()->agency->id)],
            'reception_date' => 'nullable|date',
            'kind' => ['nullable',Rule::in(array_values(config("consts.agency_consultations.KIND_LIST")))],
            'deadline' => 'nullable|date',
            'status' => ['nullable',Rule::in(array_values(config("consts.agency_consultations.STATUS_LIST")))],
            'contents' => 'nullable|max:1000',
            'updated_at' => 'nullable',
        ];
    }
    
    public function messages()
    {
        return [
            'consul_number.required' => '相談番号は必須です。',
            'title.max' => 'タイトルが長すぎます(32文字まで)。',
            'reception_date.date' => '受付日の指定が不正です。',
            'kind.in' => '種別の指定が不正です。',
            'deadline.date' => '期限の指定が不正です。',
            'status.in' => 'ステータスの指定が不正です。',
            'contents.max' => '内容が長すぎます(1000文字まで)。',
        ];
    }
}
