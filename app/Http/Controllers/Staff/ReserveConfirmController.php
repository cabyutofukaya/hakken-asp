<?php

namespace App\Http\Controllers\Staff;

use App\Models\ReserveConfirm;
use App\Http\Controllers\Controller;
use App\Services\ReserveItineraryService;
use App\Services\ReserveConfirmService;
use App\Services\EstimateService;
use App\Services\ReserveService;
use Illuminate\Http\Request;
use Gate;

/**
 * 予約確認書
 */
class ReserveConfirmController extends AppController
{
    public function __construct(ReserveItineraryService $reserveItineraryService, ReserveConfirmService $reserveConfirmService, EstimateService $estimateService, ReserveService $reserveService)
    {
        $this->reserveItineraryService = $reserveItineraryService;
        $this->reserveConfirmService = $reserveConfirmService;
        $this->estimateService = $estimateService;
        $this->reserveService = $reserveService;
    }

    /**
     * 予約確認書
     *
     * @param string $applicationStep 申込段階
     * @param string $controlNumber 管理番号(見積/予約)
     * @param string $itineraryNumber 行程番号
     */
    public function create(string $agencyAccount, string $applicationStep, string $controlNumber, string $itineraryNumber)
    {
        $reserveNumber = null;
        $estimateNumber = null;

        // 見積or予約で処理を分ける
        /**
         * リレーションは 「旅行日 > 詳細スケジュール > 仕入科目 > 仕入料金 > 参加者データ > ユーザーデータ」まで一気に取得。subjectableリエーションを使うとカスタム項目が取得できないので、「オプション科目」「航空券科目」「ホテル科目」それぞれのリレーションを設定
         */
        if ($applicationStep == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積

            // 予約情報
            $reserve = $this->estimateService->findByEstimateNumber($controlNumber, $agencyAccount);

            $reserveItinerary = $this->reserveItineraryService->findByItineraryNumber(
                $reserve->id,
                $itineraryNumber,
                $reserve->agency_id,
                [
                    'reserve_travel_dates.reserve_schedules.reserve_purchasing_subject_options.reserve_participant_prices.participant.user',
                    'reserve_travel_dates.reserve_schedules.reserve_purchasing_subject_hotels.reserve_participant_prices.participant.user',
                    'reserve_travel_dates.reserve_schedules.reserve_purchasing_subject_airplanes.reserve_participant_prices.participant.user',
                    'reserve_travel_dates.reserve_schedules.reserve_purchasing_subject_hotels.room_types', // 部屋タイプ
                    'reserve_travel_dates.reserve_schedules.reserve_purchasing_subject_airplanes.airline_companies', // 航空会社
                ]
            );

            $estimateNumber = $controlNumber;

        } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約

            $reserve = $this->reserveService->findByControlNumber($controlNumber, $agencyAccount);

            $reserveItinerary = $this->reserveItineraryService->findByItineraryNumber(
                $reserve->id,
                $itineraryNumber,
                $reserve->agency_id,
                [
                    'reserve_travel_dates.reserve_schedules.reserve_purchasing_subject_options.reserve_participant_prices.participant.user',
                    'reserve_travel_dates.reserve_schedules.reserve_purchasing_subject_hotels.reserve_participant_prices.participant.user',
                    'reserve_travel_dates.reserve_schedules.reserve_purchasing_subject_airplanes.reserve_participant_prices.participant.user',
                    'reserve_travel_dates.reserve_schedules.reserve_purchasing_subject_hotels.room_types', // 部屋タイプ
                    'reserve_travel_dates.reserve_schedules.reserve_purchasing_subject_airplanes.airline_companies', // 航空会社
                    
                ]
            );
            $reserveNumber = $controlNumber;
        } else {
            abort(404);
        }

        if (!$reserveItinerary) {
            return response("データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。", 404);
        }

        // // 認可チェック
        // $response = Gate::inspect('view', [$reserveItinerary]);
        // if (!$response->allowed()) {
        //     abort(403);
        // }

        // 認可チェック
        $response = \Gate::inspect('create', [new ReserveConfirm, $reserveItinerary]);
        if (!$response->allowed()) {
            abort(403);
        }

        // viewはeditと共通
        return view('staff.reserve_confirm.edit', compact('reserve', 'reserveItinerary', 'estimateNumber', 'reserveNumber', 'itineraryNumber', 'applicationStep'));
    }

    /**
     * 編集ページ
     *
     * @param string $confirmNumber 確認番号
     */
    public function edit(string $agencyAccount, string $applicationStep, string $controlNumber, string $itineraryNumber, string $confirmNumber)
    {
        $reserveNumber = null;
        $estimateNumber = null;

        if ($applicationStep == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積
            $reserve = $this->estimateService->findByEstimateNumber($controlNumber, $agencyAccount);

            $reserveConfirm = $this->reserveConfirmService->findByConfirmNumberForReserve($confirmNumber, $reserve, $itineraryNumber);
            $estimateNumber = $controlNumber;

        } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約

            $reserve = $this->reserveService->findByControlNumber($controlNumber, $agencyAccount);

            $reserveConfirm = $this->reserveConfirmService->findByConfirmNumberForReserve($confirmNumber, $reserve, $itineraryNumber);
            $reserveNumber = $controlNumber;

        } else {
            abort(404);
        }

        if (!$reserveConfirm) {
            return response("データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。", 404);
        }

        // 認可チェック
        $response = \Gate::inspect('view', [$reserveConfirm]);
        if (!$response->allowed()) {
            abort(403);
        }

        // 行程情報。ViewComposerで使うので、変数にセット
        $reserveItinerary = $reserveConfirm->reserve_itinerary;

        return view('staff.reserve_confirm.edit', compact('reserve', 'reserveConfirm', 'estimateNumber', 'reserveNumber', 'itineraryNumber', 'confirmNumber', 'applicationStep', 'reserveItinerary'));
    }

}
