<?php

namespace App\Http\Controllers\Staff\Web;

use App\Events\CreateItineraryEvent;
use App\Events\ReserveChangeSumGrossEvent;
use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\ReserveItineraryStoreRequest;
use App\Http\Requests\Staff\ReserveItineraryUpdateRequest;
use App\Models\ReserveItinerary;
use App\Services\ReserveConfirmService;
use App\Services\ReserveInvoiceService;
use App\Services\ReserveItineraryService;
use App\Services\ReserveService;
use App\Services\WebEstimateService;
use App\Services\WebReserveService;
use App\Services\EstimateService;
use App\Http\Controllers\Staff\AppController;
use Exception;
use Gate;
use Illuminate\Http\Request;

/**
 * 予約
 * 日程管理
 */
class ReserveItineraryController extends AppController
{
    public function __construct(WebReserveService $webReserveService, ReserveItineraryService $reserveItineraryService, ReserveConfirmService $reserveConfirmService, ReserveInvoiceService $reserveInvoiceService,WebEstimateService $webEstimateService, EstimateService $estimateService, ReserveService $reserveService)
    {
        $this->webReserveService = $webReserveService;
        $this->reserveItineraryService = $reserveItineraryService;
        $this->reserveConfirmService = $reserveConfirmService;
        $this->reserveInvoiceService = $reserveInvoiceService;
        $this->webEstimateService = $webEstimateService;
        $this->estimateService = $estimateService;
        $this->reserveService = $reserveService;
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
            $reserve = $this->webEstimateService->findByEstimateNumber($controlNumber, $agencyAccount, ['participants.user']);
        } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約
            $reserve = $this->webReserveService->findByControlNumber($controlNumber, $agencyAccount, ['participants.user']);
        } else {
            abort(404);
        }

        // 認可チェック。createで認可処理をするとキャンセル予約の場合に本ページが開けなくなってしまうので緩めの権限(viewAny)で設定
        $response = Gate::inspect('viewAny', [new ReserveItinerary]);
        if (!$response->allowed()) {
            abort(403);
        }

    
        return view('staff.web.reserve_itinerary.create', compact('reserve'));
    }

    // App\Http\Controllers\Staff\Api\ReserveItineraryControllerに移動
    // /**
    //  * Store a newly created resource in storage.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @return \Illuminate\Http\Response
    //  */
    // public function store(ReserveItineraryStoreRequest $request, string $agencyAccount, string $applicationStep, string $controlNumber)
    // {
    //     // 見積or予約で処理を分ける
    //     if ($applicationStep == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積
    //         $reserve = $this->webEstimateService->findByEstimateNumber($controlNumber, $agencyAccount);
    //     } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約
    //         $reserve = $this->webReserveService->findByControlNumber($controlNumber, $agencyAccount);
    //     } else {
    //         abort(404);
    //     }

    //     if (!$reserve) {
    //         return response("データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。", 404);
    //     }

    //     // 認可チェック
    //     $response = Gate::inspect('create', [new ReserveItinerary, $reserve]);
    //     if (!$response->allowed()) {
    //         return $this->forbiddenRedirect($response->message());
    //     }

    //     try {
    //         $input = $request->all();

    //         $reserveItinerary = \DB::transaction(function () use ($reserve, $input) {
    //             $reserveItinerary = $this->reserveItineraryService->create($reserve, $input);

    //             if ($reserveItinerary->enabled) {
    //                 event(new ReserveChangeSumGrossEvent($reserveItinerary)); // 旅行代金変更イベント
    //             }

    //             event(new CreateItineraryEvent($reserveItinerary)); // 行程作成時イベント

    //             return $reserveItinerary;
    //         });

    //         if ($reserveItinerary) {
    //             if ($applicationStep == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積
    //                 return redirect()->route('staff.web.estimates.normal.show', ['agencyAccount' => $agencyAccount, 'estimateNumber' => $controlNumber, 'tab' => config('consts.reserves.TAB_RESERVE_DETAIL')])->with('success_message', "行程「{$reserveItinerary->control_number}」を登録しました");
    //             } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約
    //                 return redirect()->route('staff.web.estimates.reserve.show', ['agencyAccount' => $agencyAccount, 'reserveNumber' => $controlNumber, 'tab' => config('consts.reserves.TAB_RESERVE_DETAIL')])->with('success_message', "行程「{$reserveItinerary->control_number}」を登録しました");
    //             }
    //         }
    //     } catch (Exception $e) {
    //         \Log::error($e);
    //     }
    //     abort(500);
    // }

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

            $reserve = $this->webEstimateService->findByEstimateNumber($controlNumber, $agencyAccount);

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
            $reserve = $this->webReserveService->findByControlNumber($controlNumber, $agencyAccount);

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

        return view('staff.web.reserve_itinerary.edit', compact('reserveItinerary'));
    }

    // App\Http\Controllers\Staff\Api\ReserveItineraryControllerに移動
    // /**
    //  * Update the specified resource in storage.
    //  *
    //  * @param  \Illuminate\Http\Request  $request
    //  * @param  int  $id
    //  * @return \Illuminate\Http\Response
    //  */
    // public function update(ReserveItineraryUpdateRequest $request, string $agencyAccount, string $applicationStep, string $controlNumber, string $itineraryNumber)
    // {
    //     // 見積or予約で処理を分ける
    //     if ($applicationStep == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積
    //         $reserve = $this->webEstimateService->findByEstimateNumber($controlNumber, $agencyAccount);

    //         $reserveItinerary = $this->reserveItineraryService->findByItineraryNumber($reserve->id, $itineraryNumber, $reserve->agency_id);
    //     } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約
    //         $reserve = $this->webReserveService->findByControlNumber($controlNumber, $agencyAccount);

    //         $reserveItinerary = $this->reserveItineraryService->findByItineraryNumber($reserve->id, $itineraryNumber, $reserve->agency_id);
    //     } else {
    //         abort(404);
    //     }

    //     // 認可チェック
    //     $response = Gate::inspect('update', [$reserveItinerary]);
    //     if (!$response->allowed()) {
    //         return $this->forbiddenRedirect($response->message());
    //     }

    //     try {
    //         $input = $request->all();

    //         $reserveItinerary = \DB::transaction(function () use ($reserveItinerary, $input) {
    //             $newReserveItinerary = $this->reserveItineraryService->update($reserveItinerary->id, $input);

    //             if ($newReserveItinerary->enabled) {
    //                 event(new ReserveChangeSumGrossEvent($newReserveItinerary)); // 旅行代金変更イベント
    //             }

    //             return $newReserveItinerary;
    //         });

    //         if ($reserveItinerary) {
    //             if ($applicationStep == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積
    //                 return redirect()->route('staff.web.estimates.normal.show', ['agencyAccount' => $agencyAccount, 'estimateNumber' => $controlNumber, 'tab' => config('consts.reserves.TAB_RESERVE_DETAIL')])->with('success_message', "行程「{$reserveItinerary->control_number}」を更新しました");
    //             } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約
    //                 // 催行済みか否かでリダイレクト先変更
    //                 if ($reserveItinerary->reserve->is_departed) {
    //                     return redirect()->route('staff.estimates.departed.show', ['agencyAccount' => $agencyAccount, 'reserveNumber' => $controlNumber, 'tab' => config('consts.reserves.TAB_RESERVE_DETAIL')])->with('success_message', "行程「{$reserveItinerary->control_number}」を更新しました");
    //                 } else {
    //                     return redirect()->route('staff.web.estimates.reserve.show', ['agencyAccount' => $agencyAccount, 'reserveNumber' => $controlNumber, 'tab' => config('consts.reserves.TAB_RESERVE_DETAIL')])->with('success_message', "行程「{$reserveItinerary->control_number}」を更新しました");
    //                 }
    //             }
    //         }
    //     } catch (ExclusiveLockException $e) { // 同時編集エラー
    //         return back()->withInput()->with('error_message', "他のユーザーによる編集済みレコードです。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
    //     } catch (Exception $e) {
    //         \Log::error($e);
    //     }
    //     abort(500);
    // }

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
            $reserve = $this->webEstimateService->findByEstimateNumber($controlNumber, $agencyAccount);

            $reserveItinerary = $this->reserveItineraryService->findByItineraryNumber($reserve->id, $itineraryNumber, $reserve->agency_id);
        } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約
            $reserve = $this->webReserveService->findByControlNumber($controlNumber, $agencyAccount);

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

        $pdf = \PDF::loadView('staff.web.reserve_itinerary.pdf.itinerary', compact('reserveItinerary'));
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
            $reserve = $this->webEstimateService->findByEstimateNumber($controlNumber, $agencyAccount);
        } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約
            $reserve = $this->webReserveService->findByControlNumber($controlNumber, $agencyAccount);
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
        $name = $request->input('name'); // ホテル名
        $roomNumbers = $request->input('rn', []); // 部屋番号リスト
        $participantIds = $request->input('pi', []); // 参加者IDリスト

        $pdf = \PDF::loadView('staff.web.reserve_itinerary.pdf.rooming_list', compact('reserve', 'date', 'name', 'roomNumbers', 'participantIds'));
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

            $reserve = $this->webEstimateService->findByEstimateNumber($controlNumber, $agencyAccount);

            $reserveItinerary = $this->reserveItineraryService->findByItineraryNumber(
                $reserve->id,
                $itineraryNumber,
                $reserve->agency_id,
                ['reserve_travel_dates.reserve_schedules.reserve_purchasing_subject_hotels.reserve_participant_prices.participant','reserve_travel_dates.reserve_schedules.reserve_purchasing_subject_hotels.room_types']
            );
        } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約
            $reserve = $this->webReserveService->findByControlNumber($controlNumber, $agencyAccount);

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

        $pdf = \PDF::loadView('staff.web.reserve_itinerary.pdf.itinerary_rooming_list', compact('reserveItinerary'));
        return $pdf->inline();  //ブラウザで開く
    }
}
