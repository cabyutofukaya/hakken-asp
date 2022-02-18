<?php

namespace App\Http\Controllers\Staff\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Staff\AccountPayable\IndexResource;
use App\Models\AccountPayable;
use App\Services\AccountPayableService;
use App\Services\ReserveItineraryService;
use App\Services\EstimateService;
use App\Services\ReserveService;
use App\Services\WebEstimateService;
use App\Services\WebReserveService;
use Illuminate\Http\Request;

/**
 * 仕入れ先買掛金
 */
class AccountPayableController extends Controller
{
    public function __construct(AccountPayableService $accountPayableService, ReserveItineraryService $reserveItineraryService, EstimateService $estimateService, ReserveService $reserveService, WebEstimateService $webEstimateService, WebReserveService $webReserveService)
    {
        $this->accountPayableService = $accountPayableService;
        $this->reserveItineraryService = $reserveItineraryService;
        $this->estimateService = $estimateService;
        $this->reserveService = $reserveService;
        $this->webEstimateService = $webEstimateService;
        $this->webReserveService = $webReserveService;
    }

    /**
     * 一覧
     */
    public function index(string $agencyAccount, string $reception, string $applicationStep, string $controlNumber, string $itineraryNumber)
    {
        // 認可チェック
        $response = \Gate::authorize('viewAny', new AccountPayable);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        // 受付種別で分ける
        if ($reception === config('consts.const.RECEPTION_TYPE_ASP')) { // ASP受付
            // 見積or予約で処理を分ける
            if ($applicationStep == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積
                $reserve = $this->estimateService->findByEstimateNumber($controlNumber, $agencyAccount);

                $reserveItinerary = $this->reserveItineraryService->findByItineraryNumber($reserve->id, $itineraryNumber, $reserve->agency_id, [], ['id'], false);
            } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約
                
                $reserve = $this->reserveService->findByControlNumber($controlNumber, $agencyAccount);
                
                $reserveItinerary = $this->reserveItineraryService->findByItineraryNumber($reserve->id, $itineraryNumber, $reserve->agency_id, [], ['id'], false);
            } else {
                abort(404);
            }
        } elseif ($reception === config('consts.const.RECEPTION_TYPE_WEB')) { // WEB受付
            // 見積or予約で処理を分ける
            if ($applicationStep == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積
                $reserve = $this->webEstimateService->findByEstimateNumber($controlNumber, $agencyAccount);

                $reserveItinerary = $this->reserveItineraryService->findByItineraryNumber($reserve->id, $itineraryNumber, $reserve->agency_id, [], ['id'], false);
            } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約
                
                $reserve = $this->webReserveService->findByControlNumber($controlNumber, $agencyAccount);
                
                $reserveItinerary = $this->reserveItineraryService->findByItineraryNumber($reserve->id, $itineraryNumber, $reserve->agency_id, [], ['id'], false);
            } else {
                abort(404);
            }
        } else {
            abort(404);
        }

        if (!$reserveItinerary) {
            return response("データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。", 404);
        }

        return IndexResource::collection(
            $this->accountPayableService->getByReserveItineraryId(
                $reserveItinerary->id
            )
        );
    }
}
