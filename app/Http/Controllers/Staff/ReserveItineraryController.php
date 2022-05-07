<?php

namespace App\Http\Controllers\Staff;

use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Models\ReserveItinerary;
use App\Services\EstimateService;
use App\Services\ReserveConfirmService;
use App\Services\ReserveInvoiceService;
use App\Services\ReserveItineraryService;
use App\Services\ReserveService;
use Exception;
use Gate;
use Illuminate\Http\Request;

/**
 * 予約
 * 日程管理
 */
class ReserveItineraryController extends AppController
{
    public function __construct(ReserveService $reserveService, ReserveItineraryService $reserveItineraryService, EstimateService $estimateService, ReserveConfirmService $reserveConfirmService, ReserveInvoiceService $reserveInvoiceService)
    {
        $this->reserveService = $reserveService;
        $this->reserveItineraryService = $reserveItineraryService;
        $this->estimateService = $estimateService;
        $this->reserveConfirmService = $reserveConfirmService;
        $this->reserveInvoiceService = $reserveInvoiceService;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param string $applicationStep 申し込み段階
     * @param string $controlNumber 予約/見積番号
     * @return \Illuminate\Http\Response
     */
    public function create($agencyAccount, string $applicationStep, string $controlNumber)
    {
        // 見積or予約で処理を分ける
        if ($applicationStep == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積
            $reserve = $this->estimateService->findByEstimateNumber($controlNumber, $agencyAccount, ['participants.user']);
        } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約
            $reserve = $this->reserveService->findByControlNumber($controlNumber, $agencyAccount, ['participants.user']);
        } else {
            abort(404);
        }

        // 認可チェック。createで認可処理をするとキャンセル予約の場合に本ページが開けなくなってしまうので緩めの権限(viewAny)で設定
        $response = Gate::inspect('viewAny', [new ReserveItinerary]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.reserve_itinerary.create', compact('reserve'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  string  $agencyAccount 会社アカウント
     * @param string $applicationStep 申し込み段階
     * @param string $controlNumber 管理番号
     * @param string $itineraryNumber 行程番号
     * @return \Illuminate\Http\Response
     */
    public function edit(string $agencyAccount, string $applicationStep, string $controlNumber, $itineraryNumber)
    {
        // 見積or予約で処理を分ける
        if ($applicationStep == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積
            $reserve = $this->estimateService->findByEstimateNumber($controlNumber, $agencyAccount);

            $reserveItinerary = $this->reserveItineraryService->findByItineraryNumber(
                $reserve->id,
                $itineraryNumber,
                $reserve->agency_id,
                [
                    'reserve_travel_dates.reserve_schedules.reserve_schedule_photos', // 写真
                    'reserve_travel_dates.reserve_schedules.reserve_purchasing_subjects.subjectable.reserve_participant_prices.participant.user',
                    'reserve_travel_dates.reserve_schedules.reserve_purchasing_subjects.subjectable.v_reserve_purchasing_subject_custom_values', // オプション科目のカスタム項目
                ]
            );

        // 旅行日 > 行程詳細 > 仕入科目 > 参加者料金 > 参加者
        } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約

            $reserve = $this->reserveService->findByControlNumber($controlNumber, $agencyAccount);

            $reserveItinerary = $this->reserveItineraryService->findByItineraryNumber(
                $reserve->id,
                $itineraryNumber,
                $reserve->agency_id,
                [
                    'reserve_travel_dates.reserve_schedules.reserve_schedule_photos', // 写真
                    'reserve_travel_dates.reserve_schedules.reserve_purchasing_subjects.subjectable.reserve_participant_prices.participant.user',
                    'reserve_travel_dates.reserve_schedules.reserve_purchasing_subjects.subjectable.v_reserve_purchasing_subject_custom_values', // オプション科目のカスタム項目
                ]
            );
        // 旅行日 > 行程詳細 > 仕入科目 > 参加者料金 > 参加者
        } else {
            abort(404);
        }

        // 認可チェック
        $response = Gate::inspect('view', [$reserveItinerary]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.reserve_itinerary.edit', compact('reserveItinerary'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  string  $agencyAccount 会社アカウント
     * @param string $applicationStep 申し込み段階
     * @param string $controlNumber 予約/見積番号
     * @param string $itineraryNumber 行程番号
     * @return \Illuminate\Http\Response
     */
    public function schedulePdf(string $agencyAccount, string $applicationStep, string $controlNumber, string $itineraryNumber)
    {
        // 見積or予約で処理を分ける
        if ($applicationStep == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積
            $reserve = $this->estimateService->findByEstimateNumber($controlNumber, $agencyAccount);

            $reserveItinerary = $this->reserveItineraryService->findByItineraryNumber($reserve->id, $itineraryNumber, $reserve->agency_id);
        } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約

            $reserve = $this->reserveService->findByControlNumber($controlNumber, $agencyAccount);

            $reserveItinerary = $this->reserveItineraryService->findByItineraryNumber($reserve->id, $itineraryNumber, $reserve->agency_id);
        } else {
            abort(404);
        }

        if (!$reserveItinerary) {
            return response("データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。", 404);
        }

        // 認可チェック
        $response = Gate::inspect('view', [$reserveItinerary]);
        if (!$response->allowed()) {
            abort(403, 'Unauthorized action.');
        }

        $pdf = \PDF::loadView('staff.reserve_itinerary.pdf.itinerary', compact('reserveItinerary'));
        return $pdf->inline("{$controlNumber}.pdf");  //ブラウザ上で開ける
    }


    /**
     * 当該宿泊施設当該日のルーミングリストを生成（PDF）
     *
     * @param string $controlNumber 予約番号
     */
    public function roomingListPdf(Request $request, string $agencyAccount, string $applicationStep, string $controlNumber)
    {
        // 見積or予約で処理を分ける
        if ($applicationStep == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積
            $reserve = $this->estimateService->findByEstimateNumber($controlNumber, $agencyAccount);
        } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約
            $reserve = $this->reserveService->findByControlNumber($controlNumber, $agencyAccount);
        } else {
            abort(404);
        }

        if (!$reserve) {
            return response("データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。", 404);
        }

        // 認可チェック
        $response = Gate::inspect('view', [$reserve]);
        if (!$response->allowed()) {
            abort(403, 'Unauthorized action.');
        }

        $date = $request->input('dt'); // 旅行日
        $hotelName = $request->input('hotel_name'); // ホテル名
        $roomNumbers = $request->input('rn', []); // 部屋番号リスト
        $participantIds = $request->input('pi', []); // 参加者IDリスト

        $pdf = \PDF::loadView('staff.reserve_itinerary.pdf.rooming_list', compact('reserve', 'date', 'hotelName', 'roomNumbers', 'participantIds'));
        return $pdf->inline();  //ブラウザで開く
    }

    /**
     * 当該行程のルーミングリストを生成（PDF）
     *
     * @param string $controlNumber 予約番号
     */
    public function itineraryRoomingListPdf(
        Request $request,
        string $agencyAccount,
        string $applicationStep,
        string $controlNumber,
        string $itineraryNumber
    ) {
        // 見積or予約で処理を分ける
        if ($applicationStep == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積
            $reserve = $this->estimateService->findByEstimateNumber($controlNumber, $agencyAccount);

            $reserveItinerary = $this->reserveItineraryService->findByItineraryNumber(
                $reserve->id,
                $itineraryNumber,
                $reserve->agency_id,
                ['reserve_travel_dates.reserve_schedules.reserve_purchasing_subject_hotels.reserve_participant_prices.participant','reserve_travel_dates.reserve_schedules.reserve_purchasing_subject_hotels.room_types']
            );
        } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約
            
            $reserve = $this->reserveService->findByControlNumber($controlNumber, $agencyAccount);

            $reserveItinerary = $this->reserveItineraryService->findByItineraryNumber(
                $reserve->id,
                $itineraryNumber,
                $reserve->agency_id,
                ['reserve_travel_dates.reserve_schedules.reserve_purchasing_subject_hotels.reserve_participant_prices.participant','reserve_travel_dates.reserve_schedules.reserve_purchasing_subject_hotels.room_types']
            );
        } else {
            abort(404);
        }

        if (!$reserveItinerary) {
            return response("データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。", 404);
        }

        // 認可チェック
        $response = Gate::inspect('view', [$reserveItinerary]);
        if (!$response->allowed()) {
            abort(403, 'Unauthorized action.');
        }

        $pdf = \PDF::loadView('staff.reserve_itinerary.pdf.itinerary_rooming_list', compact('reserveItinerary'));
        return $pdf->inline();  //ブラウザで開く
    }
}
