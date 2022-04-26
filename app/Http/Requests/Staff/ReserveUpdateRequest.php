<?php

namespace App\Http\Requests\Staff;

use App\Models\Reserve;
use App\Rules\CheckTravelPeriod;
use App\Rules\ExistApplicantCustomer;
use App\Rules\ExistArea;
use App\Rules\ExistStaff;
use App\Rules\CheckScheduleChange;
use App\Services\AgencyWithdrawalService;
use App\Services\BusinessUserManagerService;
use App\Services\ReserveEstimateService;
use App\Services\ReservePurchasingSubjectService;
use App\Services\ReserveScheduleService;
use App\Services\ReserveService;
use App\Services\ReserveTravelDateService;
use App\Services\UserService;
use App\Traits\ReserveTrait;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReserveUpdateRequest extends FormRequest
{
    use ReserveTrait;

    public function __construct(ReserveService $reserveService, ReserveTravelDateService $reserveTravelDateService, ReserveEstimateService $reserveEstimateService, BusinessUserManagerService $businessUserManagerService, UserService $userService, ReserveScheduleService $reserveScheduleService, ReservePurchasingSubjectService $reservePurchasingSubjectService, AgencyWithdrawalService $agencyWithdrawalService)
    {
        $this->reserveService = $reserveService;
        $this->reserveTravelDateService = $reserveTravelDateService;
        $this->reserveEstimateService = $reserveEstimateService;
        $this->businessUserManagerService = $businessUserManagerService;
        $this->userService = $userService;
        $this->reserveScheduleService = $reserveScheduleService;
        $this->reservePurchasingSubjectService = $reservePurchasingSubjectService;
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
            'reserve_number' => $this->reserveNumber,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $agencyId = auth('staff')->user()->agency->id;

        $reserve = Reserve::where('control_number', $this->reserveNumber)->where('agency_id', auth('staff')->user()->agency->id)->firstOrFail(); // 現状の予約データ

        return [
            'reserve_number' => 'required',
            'participant_type' => ['required',Rule::in(array_values(config("consts.reserves.PARTICIPANT_TYPE_LIST")))],
            'applicant_user_number' => ['required',new ExistApplicantCustomer($agencyId, $this->participant_type),function ($attribute, $value, $fail) use ($reserve) {

                // 入金登録がなければ申込者の変更チェックは不要（sum_depositは当該予約の入金合計）
                if ($reserve->sum_deposit === 0) {
                    return;
                }

                /**
                 * 入金登録がある場合は申込者情報が変更されているかチェック
                 *
                 * 申込者情報が変更されている場合は更新不可。
                 * ただし、同一法人で担当者が変わるケースは入金レコードが存在していても変更可
                 */
                $applicantInfo = $this->getApplicantCustomerIdInfo($this->agencyAccount, $this->participant_type, $value, $this->userService, $this->businessUserManagerService);

                if ($reserve->applicantable_type !== $applicantInfo['applicantable_type'] || $reserve->applicantable_id !== $applicantInfo['applicantable_id']) { // 申込者情報が変更

                    // 変更後の申込者情報
                    if ($applicantInfo['applicantable_type'] === 'App\Models\BusinessUserManager') { // 法人顧客ユーザー
                        $newApplicant = $this->businessUserManagerService->find($applicantInfo['applicantable_id'], [], [], true); // 一応、削除済みも取得
                    } elseif ($applicantInfo['applicantable_type'] === 'App\Models\User') { // 個人ユーザー
                        $newApplicant = $this->userService->find($applicantInfo['applicantable_id'], [], [], true); // 一応、削除済みも取得
                    }

                    if ($reserve->applicantable->applicant_type === config('consts.reserves.PARTICIPANT_TYPE_BUSINESS') && $newApplicant->applicant_type === config('consts.reserves.PARTICIPANT_TYPE_BUSINESS') && $reserve->applicantable->business_user_id === $newApplicant->business_user_id) {
                        // 同じ会社で担当者が変わるケース。この場合は入金レコードがあっても問題ナシ
                    } else {
                        // 上記以外。入金登録があり、且つ申込者情報が変更されているケース
                        $fail("入金登録があるため申込者情報を変更できません。");
                    }
                }
            }],
            'name' => 'nullable|max:100',
            'departure_date' => 'required|date',
            'return_date' => ['required','date','after_or_equal:departure_date',new CheckScheduleChange($this->departure_date, $reserve, $this->agencyWithdrawalService)],
            // 'return_date' => ['required','date','after_or_equal:departure_date',function ($attribute, $value, $fail) use ($reserve) { // 旅行日が既存の日程よりも短くなった場合(出発日が延びた or 帰着日の前倒し)、無くなった日付の中で出金登録がある場合はエラー
            //     if ($reserve->departure_date < $this->departure_date || $reserve->return_date > $this->return_date) {

            //         // 日付比較のためPOSTデータから一時的なReserveモデルを作成
            //         $rsv = new Reserve([
            //             'departure_date' => $this->departure_date,
            //             'return_date' => $this->return_date
            //         ]);

            //         $oldTravelDates = $this->getTravelDates($reserve, 'Y/m/d'); // 既存の旅行日一覧
            //         $newTravelDates = $this->getTravelDates($rsv, 'Y/m/d'); // POSTされた旅行日一覧
            //         if ($deletedDays = array_diff($oldTravelDates, $newTravelDates)) { // 削除日あり

            //             // 当該予約IDに紐づく出金レコードの日付一覧を取得（結果リストから「日付を抽出」→「重複を弾く」）
            //             $withDrawalDateArr = $this->agencyWithdrawalService->getByReserveId($reserve->id, ['reserve_travel_date:id,travel_date'], ['id','reserve_travel_date_id'])
            //             ->pluck('reserve_travel_date.travel_date')->unique()->all();

            //             $errDateArr = array_intersect($deletedDays, $withDrawalDateArr);
            //             if ($errDateArr) { // 削除した日付に出金日あり
            //                 sort($errDateArr);
            //                 $fail("変更した旅行日の中に出金済み仕入情報があるため更新できません(" . implode(",", $errDateArr) . ")。支払管理より当該商品の出金履歴を削除してから変更してください。");
            //             }
            //         }
            //     }
            // }],
            'departure_id' => ['nullable',new ExistArea($agencyId)],
            'departure_place' => 'nullable|max:100',
            'destination_id' => ['nullable',new ExistArea($agencyId)],
            'destination_place' => 'nullable|max:100',
            'note' => 'nullable|max:100',
            'manager_id' => ['nullable', new ExistStaff($agencyId)],
            'updated_at' => 'nullable',
        ];
    }
    
    public function messages()
    {
        return [
            'reserve_number.required' => '予約番号は必須です。',
            'participant_type.required' => '顧客種別は必須です。',
            'applicant_user_number.required' => '顧客が選択されていません。',
            'name.max' => '旅行名が長すぎます(100文字まで)。',
            'departure_date.required' => '出発日は必須です。',
            'departure_date.date' => '出発日の入力形式が不正です(YYYY-MM-DD)。',
            'return_date.required' => '帰着日は必須です。',
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
