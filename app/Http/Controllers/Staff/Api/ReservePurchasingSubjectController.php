<?php

namespace App\Http\Controllers\Staff\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\ReservePurchasingSubjectService;
use App\Services\ReserveParticipantOptionPriceService;
use App\Services\ReserveParticipantAirplanePriceService;
use App\Services\ReserveParticipantHotelPriceService;

/**
 * 行程仕入
 */
class ReservePurchasingSubjectController extends Controller
{
    public function __construct(ReservePurchasingSubjectService $reservePurchasingSubjectService, ReserveParticipantOptionPriceService $reserveParticipantOptionPriceService, ReserveParticipantAirplanePriceService $reserveParticipantAirplanePriceService, ReserveParticipantHotelPriceService $reserveParticipantHotelPriceService)
    {
        $this->reservePurchasingSubjectService = $reservePurchasingSubjectService;
        $this->reserveParticipantOptionPriceService = $reserveParticipantOptionPriceService;
        $this->reserveParticipantAirplanePriceService = $reserveParticipantAirplanePriceService;
        $this->reserveParticipantHotelPriceService = $reserveParticipantHotelPriceService;
    }

    /**
     * 当該スケジュールに出金登録がされている場合はyes
     * 
     * @param int $reserveScheduleId スケジュールID
     */
    public function existScheduleWithdrawal(string $agencyAccount, int $reserveScheduleId)
    {
        $result = $this->reservePurchasingSubjectService->existWithdrawalHistoryByReserveScheduleId($reserveScheduleId);

        return response($result ? 'yes' : 'no', 200);
    }

    /**
     * 当該科目に出金登録がされている場合はyes
     */
    public function existSubjectWithdrawal(string $agencyAccount, string $subject, int $id)
    {
        $result = null;
        if ($subject === config('consts.subject_categories.SUBJECT_CATEGORY_OPTION')) {
            $result = $this->reserveParticipantOptionPriceService->existWithdrawalHistoryByReservePurchasingSubjectOptionId($id);
        } elseif ($subject === config('consts.subject_categories.SUBJECT_CATEGORY_AIRPLANE')) {
            $result = $this->reserveParticipantAirplanePriceService->existWithdrawalHistoryByReservePurchasingSubjectAirplaneId($id);
        } elseif ($subject === config('consts.subject_categories.SUBJECT_CATEGORY_HOTEL')) {
            $result = $this->reserveParticipantHotelPriceService->existWithdrawalHistoryByReservePurchasingSubjectHotelId($id);
        } else {
            abort(400);
        }
        
        return response($result ? 'yes' : 'no', 200);
    }
}
