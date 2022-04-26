<?php

namespace App\Http\Requests\Staff;

use App\Models\Reserve;
use App\Rules\CheckScheduleChange;
use App\Services\ReserveService;
use App\Services\WebReserveService;
use App\Services\AgencyWithdrawalService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckReserveScheduleChangeRequest extends FormRequest
{
    public function __construct(ReserveService $reserveService, WebReserveService $webReserveService, AgencyWithdrawalService $agencyWithdrawalService)
    {
        $this->reserveService = $reserveService;
        $this->webReserveService = $webReserveService;
        $this->agencyWithdrawalService = $agencyWithdrawalService;
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
            'control_number' => $this->controlNumber,
            'reception' => $this->reception,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $reserve = null;
        // 受付種別で分ける
        if ($this->reception === config('consts.const.RECEPTION_TYPE_ASP')) { // ASP受付
            $reserve = $this->reserveService->findByControlNumber($this->controlNumber, $this->agencyAccount);
        } elseif ($this->reception === config('consts.const.RECEPTION_TYPE_WEB')) { // WEB受付
            $reserve = $this->webReserveService->findByControlNumber($this->controlNumber, $this->agencyAccount);
        } else {
            abort(400, "パラメータエラーです");
        }
        
        return [
            'control_number' => 'required',
            'reception' => 'required',
            'departure_date' => 'required|date',
            'return_date' => ['required','date','after_or_equal:departure_date',new CheckScheduleChange($this->departure_date, $reserve, $this->agencyWithdrawalService)],
        ];
    }
    
    public function messages()
    {
        return [
            'control_number.required' => '予約管理番号は必須です。',
            'reception.required' => '受付種別は必須です。',
            'departure_date.required' => '出発日は必須です。',
            'departure_date.date' => '出発日の入力形式が不正です(YYYY-MM-DD)。',
            'return_date.required' => '帰着日は必須です。',
            'return_date.date' => '帰着日の入力形式が不正です(YYYY-MM-DD)。',
            'return_date.after_or_equal' => '帰着日は出発日以降の日付を指定してください。',
        ];
    }
}
