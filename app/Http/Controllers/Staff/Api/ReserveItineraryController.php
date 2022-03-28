<?php

namespace App\Http\Controllers\Staff\Api;

use App\Events\UpdateBillingAmountEvent;
use App\Events\CreateItineraryEvent;
use App\Events\ReserveChangeSumGrossEvent;
use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\ReserveItineraryDestroyRequest;
use App\Http\Requests\Staff\ReserveItineraryEnabledRequest;
use App\Http\Requests\Staff\ReserveItineraryStoreRequest;
use App\Http\Requests\Staff\ReserveItineraryUpdateRequest;
use App\Http\Resources\Staff\ReserveItinerary\IndexResource;
use App\Http\Resources\Staff\ReserveItinerary\UpdateResource;
use App\Http\Resources\Staff\ReserveItinerary\StoreResource;
use App\Models\ReserveItinerary;
use App\Services\ReserveItineraryService;
use App\Services\ReserveService;
use App\Services\EstimateService;
use App\Services\WebReserveService;
use App\Services\WebEstimateService;
use DB;
use Exception;
use Gate;
use Illuminate\Http\Request;
use Log;

/**
 * 予約
 * 行程管理
 */
class ReserveItineraryController extends Controller
{
    public function __construct(ReserveService $reserveService, ReserveItineraryService $reserveItineraryService, EstimateService $estimateService, WebReserveService $webReserveService, WebEstimateService $webEstimateService)
    {
        $this->reserveService = $reserveService;
        $this->estimateService = $estimateService;
        $this->reserveItineraryService = $reserveItineraryService;
        $this->webReserveService = $webReserveService;
        $this->webEstimateService = $webEstimateService;
    }

    /**
     * 一覧取得
     *
     * @param string $reception 受付種別(WEB or ASP)
     * @param string $applicationStep 申込段階
     * @param string $controlNumber 管理番号(見積/予約)
     * @return \Illuminate\Http\Response
     */
    public function index(string $agencyAccount, string $reception, string $applicationStep, string $controlNumber)
    {
        // 認可チェック
        $response = Gate::authorize('viewAny', new ReserveItinerary);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        // 受付種別で分ける
        if ($reception === config('consts.const.RECEPTION_TYPE_ASP')) { // ASP受付
            // 見積or予約で処理を分ける
            if ($applicationStep == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積
                $reserve = $this->estimateService->findByEstimateNumber($controlNumber, $agencyAccount);
            } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約
                $reserve = $this->reserveService->findByControlNumber($controlNumber, $agencyAccount);
            } else {
                abort(404);
            }
        } elseif ($reception === config('consts.const.RECEPTION_TYPE_WEB')) { // WEB受付
            // 見積or予約で処理を分ける
            if ($applicationStep == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積
                $reserve = $this->webEstimateService->findByEstimateNumber($controlNumber, $agencyAccount);
            } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約
                $reserve = $this->webReserveService->findByControlNumber($controlNumber, $agencyAccount);
            } else {
                abort(404);
            }
        } else {
            abort(404);
        }

        if (!$reserve) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        return IndexResource::collection(
            $this->reserveItineraryService->getByReserveId(
                $reserve->id,
                [
                    'agency',
                    'reserve',
                    'reserve_travel_dates.reserve_schedules',
                    'reserve_participant_hotel_prices'
                ]
            )
        );
    }

    /**
     * 有効化をセット
     *
     * @param string $agencyAccount 会社アカウント
     * @param string $reception 受付種別(WEB or ASP)
     * @param string $applicationStep 申込段階
     * @param string $controlNumber 管理番号(見積/予約)
     *
     */
    public function setEnabled(ReserveItineraryEnabledRequest $request, string $agencyAccount, string $reception, string $applicationStep, string $controlNumber)
    {
        $itineraryNumber = $request->input('control_number'); // 行程番号

        // 受付種別で分ける
        if ($reception === config('consts.const.RECEPTION_TYPE_ASP')) { // ASP受付
            // 見積or予約で処理を分ける
            if ($applicationStep == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積

                $reserve = $this->estimateService->findByEstimateNumber($controlNumber, $agencyAccount);

                $reserveItinerary = $this->reserveItineraryService->findByItineraryNumber($reserve->id, $itineraryNumber, $reserve->agency_id);
            } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約
                abort(403); // 現状、予約時は有効化切り替え不可
            } else {
                abort(404);
            }
        } elseif ($reception === config('consts.const.RECEPTION_TYPE_WEB')) { // WEB受付
            // 見積or予約で処理を分ける
            if ($applicationStep == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積

                $reserve = $this->webEstimateService->findByEstimateNumber($controlNumber, $agencyAccount);

                $reserveItinerary = $this->reserveItineraryService->findByItineraryNumber($reserve->id, $itineraryNumber, $reserve->agency_id);
            } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約
                abort(403); // 現状、予約時は有効化切り替え不可
            } else {
                abort(404);
            }
        } else {
            abort(404);
        }

        // 認可チェック
        if (!$reserveItinerary) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        $response = Gate::authorize('update', $reserveItinerary);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $newReserveItinerary = DB::transaction(function () use ($reserveItinerary) {
            $newReserveItinerary = $this->reserveItineraryService->setEnabled($reserveItinerary->id, $reserveItinerary->reserve_id);

            event(new ReserveChangeSumGrossEvent($newReserveItinerary)); // 旅行代金変更イベント

            return $newReserveItinerary;
        });
        if ($newReserveItinerary) {
            return new UpdateResource($newReserveItinerary, 200);
        }
        abort(500);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ReserveItineraryStoreRequest $request, string $agencyAccount, string $reception, string $applicationStep, string $controlNumber)
    {
        // 受付種別で分ける
        if ($reception === config('consts.const.RECEPTION_TYPE_ASP')) { // ASP受付
            // 見積or予約で処理を分ける
            if ($applicationStep == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積
                $reserve = $this->estimateService->findByEstimateNumber($controlNumber, $agencyAccount);
            } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約
                $reserve = $this->reserveService->findByControlNumber($controlNumber, $agencyAccount);
            } else {
                abort(404);
            }
        } elseif ($reception === config('consts.const.RECEPTION_TYPE_WEB')) { // WEB受付
            // 見積or予約で処理を分ける
            if ($applicationStep == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積
                $reserve = $this->webEstimateService->findByEstimateNumber($controlNumber, $agencyAccount);
            } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約
                $reserve = $this->webReserveService->findByControlNumber($controlNumber, $agencyAccount);
            } else {
                abort(404);
            }
        } else {
            abort(404);
        }

        
        if (!$reserve) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('create', [new ReserveItinerary, $reserve]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $input = $request->all();

        try {
            $reserveItinerary = \DB::transaction(function () use ($reserve, $input) {
                $reserveItinerary = $this->reserveItineraryService->create($reserve, $input);

                if ($reserveItinerary->enabled) {
                    event(new ReserveChangeSumGrossEvent($reserveItinerary)); // 旅行代金変更イベント
                }

                event(new CreateItineraryEvent($reserveItinerary)); // 行程作成時イベント

                return $reserveItinerary;
            });

            if ($reserveItinerary) {
                if (request()->input("set_message")) {
                    request()->session()->flash('success_message', "「行程「{$reserveItinerary->control_number}」を登録しました"); // set_messageは処理成功のフラッシュメッセージのセットを要求するパラメータ
                }
                return new StoreResource($reserveItinerary, 201);
            }
        } catch (Exception $e) {
            \Log::error($e);
        }
        abort(500);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(ReserveItineraryUpdateRequest $request, string $agencyAccount, string $reception, string $applicationStep, string $controlNumber, string $itineraryNumber)
    {
        // 受付種別で分ける
        if ($reception === config('consts.const.RECEPTION_TYPE_ASP')) { // ASP受付
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
        } elseif ($reception === config('consts.const.RECEPTION_TYPE_WEB')) { // WEB受付
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
        } else {
            abort(404);
        }


        // 認可チェック
        $response = Gate::inspect('update', [$reserveItinerary]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        try {
            $input = $request->all();

            $reserveItinerary = \DB::transaction(function () use ($reserveItinerary, $input) {
                $newReserveItinerary = $this->reserveItineraryService->update($reserveItinerary->id, $input);

                if ($newReserveItinerary->enabled) {
                    event(new ReserveChangeSumGrossEvent($newReserveItinerary)); // 旅行代金変更イベント
                }

                event(new UpdateBillingAmountEvent($newReserveItinerary->reserve)); // 請求金額変更イベント

                return $newReserveItinerary;
            });

            if ($reserveItinerary) {
                if (request()->input("set_message")) {
                    request()->session()->flash('success_message', "行程「{$reserveItinerary->control_number}」を更新しました"); // set_messageは処理成功のフラッシュメッセージのセットを要求するパラメータ
                }
                return new UpdateResource($reserveItinerary);
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー
            abort(409, "他のユーザーによる編集済みレコードです。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        } catch (Exception $e) {
            \Log::error($e);
        }
        abort(500);
    }

    /**
     * 旅程削除処理
     *
     * @param string $agencyAccount 会社アカウント
     * @param string $reception 受付種別(WEB or ASP)
     * @param string $applicationStep 申込段階
     * @param string $controlNumber 管理番号(見積/予約)
     * @param string $itineraryNumber 行程番号
     */
    public function destroy(
        ReserveItineraryDestroyRequest $request,
        string $agencyAccount,
        string $reception,
        string $applicationStep,
        string $controlNumber,
        $itineraryNumber
    ) {
        // 受付種別で分ける
        if ($reception === config('consts.const.RECEPTION_TYPE_ASP')) { // ASP受付
            // 見積or予約で処理を分ける
            if ($applicationStep == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積
                $reserve = $this->estimateService->findByEstimateNumber($controlNumber, $agencyAccount);

                $reserveItinerary = $this->reserveItineraryService->findByItineraryNumber($reserve->id, $itineraryNumber, $reserve->agency_id);
            } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約
                abort(403); // 現状、予約時は削除処理不可
            } else {
                abort(404);
            }
        } elseif ($reception === config('consts.const.RECEPTION_TYPE_WEB')) { // WEB受付
            // 見積or予約で処理を分ける
            if ($applicationStep == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積
                $reserve = $this->webEstimateService->findByEstimateNumber($controlNumber, $agencyAccount);

                $reserveItinerary = $this->reserveItineraryService->findByItineraryNumber($reserve->id, $itineraryNumber, $reserve->agency_id);
            } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約
                abort(403); // 現状、予約時は削除処理不可
            } else {
                abort(404);
            }
        } else {
            abort(404);
        }

        // 認可チェック
        if (!$reserveItinerary) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        $response = Gate::authorize('delete', $reserveItinerary);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }


        $result = DB::transaction(function () use ($reserveItinerary) {
            $result= $this->reserveItineraryService->delete($reserveItinerary->id, true);//論理削除

            if ($reserveItinerary->enabled) {
                // 合計金額を0円にするので、予約IDだけセットしたReserveItineraryオブジェクトを渡す
                event(new ReserveChangeSumGrossEvent(new ReserveItinerary(['reserve_id' => $reserveItinerary->reserve_id]))); // 旅行代金変更イベント
            }

            return $result;
        });

        if ($result) {
            return response('', 200);
        }
        abort(404); // not found
    }
}
