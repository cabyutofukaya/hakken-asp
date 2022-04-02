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
     * 当該仕入商品が編集可能か否か
     * 出金登録がなくキャンセルユーザーがいなければ編集可
     */
    public function canItemEdit(string $agencyAccount, string $subject, int $id)
    {
        $exists1 = true;
        $exists2 = true;

        if ($subject === config('consts.subject_categories.SUBJECT_CATEGORY_OPTION')) {
            $exists1 = $this->reserveParticipantOptionPriceService->existWithdrawalHistoryByReservePurchasingSubjectOptionId($id); // 出金登録の有無を確認

            $exists2 = $this->reserveParticipantOptionPriceService->existCancelByReservePurchasingSubjectOptionId($id); // キャンセルレコードの有無を確認
        } elseif ($subject === config('consts.subject_categories.SUBJECT_CATEGORY_AIRPLANE')) {
            $exists1 = $this->reserveParticipantAirplanePriceService->existWithdrawalHistoryByReservePurchasingSubjectAirplaneId($id); // 出金登録の有無を確認

            $exists2 = $this->reserveParticipantAirplanePriceService->existCancelByReservePurchasingSubjectAirplaneId($id); // キャンセルレコードの有無を確認
        } elseif ($subject === config('consts.subject_categories.SUBJECT_CATEGORY_HOTEL')) {
            $exists1 = $this->reserveParticipantHotelPriceService->existWithdrawalHistoryByReservePurchasingSubjectHotelId($id); // 出金登録の有無を確認

            $exists2 = $this->reserveParticipantHotelPriceService->existCancelByReservePurchasingSubjectHotelId($id); // キャンセルレコードの有無を確認
        } else {
            abort(400);
        }

        if (!$exists1 && !$exists2) {
            return ['result' => 'yes'];
        } else {
            $err = [];
            if ($exists1) {
                $err[] = "出金データがあるため削除できません。支払管理より、当該商品の出金履歴を削除してからご変更ください。";
            }
            if ($exists2) {
                $err[] = "キャンセル参加者情報があるため削除できません。";
            }

            return ['result' => 'no', 'message' => implode("\n", $err)];
        }
    }
}
