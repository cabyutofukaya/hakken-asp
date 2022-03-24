<?php

namespace App\Http\Controllers\Staff\Web;

use App\Traits\CancelChargeTrait;
use App\Services\WebReserveService;
use App\Services\ParticipantService;
use App\Services\ReserveParticipantPriceService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class ParticipantController extends Controller
{
    use CancelChargeTrait;

    public function __construct(WebReserveService $webReserveService, ParticipantService $participantService, ReserveParticipantPriceService $reserveParticipantPriceService) {
        $this->webReserveService = $webReserveService;
        $this->participantService = $participantService;
        // traitで使用
        $this->reserveParticipantPriceService = $reserveParticipantPriceService;
    }

    /**
     * キャンセルチャージ設定ページ
     */
    public function cancelCharge(string $agencyAccount, string $controlNumber, int $id)
    {
        $reserve = $this->webReserveService->findByControlNumber($controlNumber, $agencyAccount);

        if (!$reserve) {
            return response("データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。", 404);
        }

        $participant = $this->participantService->find($id);
        if (!$participant) {
            return response("データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。", 404);
        }

        // 支払い情報を取得。第二引数は有効行程ID
        $purchasingList = $this->getPurchasingListByParticipant($participant->id, $reserve->enabled_reserve_itinerary->id, true);

        return view('staff.web.participant.cancel_charge', compact('participant', 'reserve', 'purchasingList'));
    }

    /**
     * キャンセルチャージ処理
     */
    public function cancelChargeUpdate(string $agencyAccount, string $controlNumber, int $id)
    {
    }

}
