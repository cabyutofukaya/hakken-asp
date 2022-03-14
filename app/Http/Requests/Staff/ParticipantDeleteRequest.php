<?php

namespace App\Http\Requests\Staff;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use App\Services\AgencyWithdrawalService;
use App\Services\ParticipantService;

class ParticipantDeleteRequest extends FormRequest
{
    public function __construct(AgencyWithdrawalService $agencyWithdrawalService, ParticipantService $participantService)
    {
        $this->agencyWithdrawalService = $agencyWithdrawalService;
        $this->participantService = $participantService;
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
            'application_step' => $this->applicationStep,
            'control_number' => $this->controlNumber,
            'id' => $this->id, // 参加者ID
        ]);
    }

    public function withValidator(Validator $validator)
    {
        // 申し込み段階が「予約」時は、削除対象参加者の仕入に対して出金があるかチェック
        $validator->sometimes('id', ['required', function ($attribute, $value, $fail) {
            $participant = $this->participantService->find($value, [], ['id','name','reserve_id']);
            if ($this->agencyWithdrawalService->isExistsReserveId($participant->reserve_id)) {
                $fail("参加者「{$participant->name}」の仕入に対する出金登録があるため削除できません。");
            }
        }], function () {
            return $this->applicationStep == config('consts.reserves.APPLICATION_STEP_RESERVE');
        });
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'application_step' => 'required',
            'control_number' => 'required',
        ];
    }
    
    public function messages()
    {
        return [
            'application_step.required' => '申し込み種別は必須です。',
            'control_number.required' => '予約/見積番号は必須です。',
            'id.required' => '参加者IDは必須です。',
        ];
    }
}
