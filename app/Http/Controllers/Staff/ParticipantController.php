<?php

namespace App\Http\Controllers\Staff;

use App\Exceptions\ExclusiveLockException;
use App\Events\PriceRelatedChangeEvent;
use App\Events\ReserveChangeHeadcountEvent;
use App\Events\ReserveChangeRepresentativeEvent;
use App\Events\UpdateBillingAmountEvent;
use App\Traits\CancelChargeTrait;
use App\Traits\ReserveItineraryTrait;
use App\Services\ReserveService;
use App\Services\ParticipantService;
use App\Services\ReserveParticipantPriceService;
use App\Services\ReserveParticipantAirplanePriceService;
use App\Services\ReserveParticipantHotelPriceService;
use App\Services\ReserveParticipantOptionPriceService;
use App\Services\AccountPayableDetailService;
use App\Services\ReserveItineraryService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ParticipantController extends Controller
{
    use CancelChargeTrait, ReserveItineraryTrait;

    public function __construct(ReserveService $reserveService, ParticipantService $participantService, ReserveParticipantPriceService $reserveParticipantPriceService, ReserveParticipantOptionPriceService $reserveParticipantOptionPriceService, ReserveParticipantAirplanePriceService $reserveParticipantAirplanePriceService, ReserveParticipantHotelPriceService $reserveParticipantHotelPriceService, AccountPayableDetailService $accountPayableDetailService, ReserveItineraryService $reserveItineraryService)
    {
        $this->reserveService = $reserveService;
        $this->participantService = $participantService;
        // traitで使用
        $this->reserveParticipantPriceService = $reserveParticipantPriceService;
        $this->reserveParticipantOptionPriceService = $reserveParticipantOptionPriceService;
        $this->reserveParticipantAirplanePriceService = $reserveParticipantAirplanePriceService;
        $this->reserveParticipantHotelPriceService = $reserveParticipantHotelPriceService;
        $this->accountPayableDetailService = $accountPayableDetailService;
        $this->reserveItineraryService = $reserveItineraryService;
    }

    /**
     * キャンセルチャージ設定ページ
     */
    public function cancelCharge(string $agencyAccount, string $controlNumber, int $id)
    {
        $reserve = $this->reserveService->findByControlNumber($controlNumber, $agencyAccount);

        if (!$reserve) {
            return response("データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。", 404);
        }

        $participant = $this->participantService->find($id);
        if (!$participant) {
            return response("データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。", 404);
        }
        
        // 支払い情報を取得。有効仕入(valid=true)のみ取得
        $purchasingList = $this->getPurchasingListByParticipant(
            $this->getPaticipantRow($participant), // ラベル情報などを付与した参加者情報
            $reserve->enabled_reserve_itinerary->id, 
            true);

        return view('staff.participant.cancel_charge', compact('participant', 'reserve', 'purchasingList'));
    }
}
