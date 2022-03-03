<?php

namespace App\Http\Controllers\Staff\Api;

use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\ReserveConfirmStoretRequest;
use App\Http\Requests\Staff\ReserveConfirmUpdateRequest;
use App\Http\Resources\Staff\ReserveConfirm\IndexResource;
use App\Http\Resources\Staff\ReserveConfirm\StoreResource;
use App\Http\Resources\Staff\ReserveConfirm\UpdateResource;
use App\Models\ReserveConfirm;
use App\Services\ReserveConfirmService;
use App\Services\ReserveItineraryService;
use App\Services\ReserveService;
use App\Services\EstimateService;
use App\Services\WebReserveService;
use App\Services\WebEstimateService;
use Illuminate\Http\Request;

/**
 * 予約
 * 行程管理
 */
class ReserveConfirmController extends Controller
{
    public function __construct(ReserveConfirmService $reserveConfirmService, ReserveItineraryService $reserveItineraryService, EstimateService $estimateService, ReserveService $reserveService, WebEstimateService $webEstimateService, WebReserveService $webReserveService)
    {
        $this->reserveConfirmService = $reserveConfirmService;
        $this->reserveItineraryService = $reserveItineraryService;
        $this->estimateService = $estimateService;
        $this->reserveService = $reserveService;
        $this->webEstimateService = $webEstimateService;
        $this->webReserveService = $webReserveService;
    }
    
    /**
     * 一覧取得
     *
     * @param string $agencyAccount 会社アカウント
     * @param string $applicationStep 申込段階（見積or予約）
     * @param string $controlNumber 管理番号（見積番号or予約番号）
     * @param string $itineraryNumber 行程番号
     * @return \Illuminate\Http\Response
     */
    public function index(string $agencyAccount, string $reception, string $applicationStep, string $controlNumber, string $itineraryNumber)
    {
        // 認可チェック
        $response = \Gate::authorize('viewAny', new ReserveConfirm);
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
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        return IndexResource::collection(
            $this->reserveConfirmService->getByReserveItineraryId(
                $reserveItinerary->id,
                ['reserve','reserve_itinerary','document_quote']
            )
        );
    }

    /**
     * 作成
     */
    public function store(ReserveConfirmStoretRequest $request, $agencyAccount, string $reception, string $applicationStep, string $controlNumber, string $itineraryNumber)
    {
        // 認可チェック
        $response = \Gate::inspect('create', new ReserveConfirm);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

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

        if (!$reserveItinerary) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        $agencyId = auth('staff')->user()->agency_id;

        $input = $request->validated();

        $input['agency_id'] = $reserveItinerary->agency_id; // 会社IDをセット
        $input['reserve_id'] = $reserveItinerary->reserve_id; // 予約ID
        $input['reserve_itinerary_id'] = $reserveItinerary->id; // 行程管理IDをセット

        try {
            $reserveConfirm = \DB::transaction(function () use ($input) {
                return $this->reserveConfirmService->create($input);
            });
            if ($reserveConfirm) {
                if (request()->input("create_pdf")) { // PDF作成
                    $pdfFile = $this->reserveConfirmService->createPdf('staff.reserve_confirm.pdf', ['reserveConfirm' => $reserveConfirm]);

                    // 作成したPDFファイル名をセット
                    $this->reserveConfirmService->setPdf($reserveConfirm, $pdfFile, $agencyId);
                }
                if (request()->input("set_message")) {
                    request()->session()->flash('success_message', "「帳票({$reserveConfirm->confirm_number})の保存処理が完了しました。"); // set_messageは処理成功のフラッシュメッセージのセットを要求するパラメータ
                }
                return new StoreResource($this->reserveConfirmService->find($reserveConfirm->id), 201);
            }
        } catch (\Exception $e) {
            // パラメータエラー等
            \Log::error($e);
        }
        abort(500);
    }

    /**
     * 更新
     */
    public function update(ReserveConfirmUpdateRequest $request, string $agencyAccount, string $reception, string $applicationStep, string $controlNumber, string $itineraryNumber, string $confirmNumber)
    {
        // 受付種別で分ける
        if ($reception === config('consts.const.RECEPTION_TYPE_ASP')) { // ASP受付
            // 見積or予約で処理を分ける
            if ($applicationStep == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積
                $reserve = $this->estimateService->findByEstimateNumber($controlNumber, $agencyAccount);

                $reserveConfirm = $this->reserveConfirmService->findByConfirmNumberForReserve($confirmNumber, $reserve, $itineraryNumber);
            } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約
                $reserve = $this->reserveService->findByControlNumber($controlNumber, $agencyAccount);

                $reserveConfirm = $this->reserveConfirmService->findByConfirmNumberForReserve($confirmNumber, $reserve, $itineraryNumber);
            } else {
                abort(404);
            }
        } elseif ($reception === config('consts.const.RECEPTION_TYPE_WEB')) { // WEB受付
            // 見積or予約で処理を分ける
            if ($applicationStep == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積
                $reserve = $this->webEstimateService->findByEstimateNumber($controlNumber, $agencyAccount);

                $reserveConfirm = $this->reserveConfirmService->findByConfirmNumberForReserve($confirmNumber, $reserve, $itineraryNumber);
            } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約
                $reserve = $this->webReserveService->findByControlNumber($controlNumber, $agencyAccount);

                $reserveConfirm = $this->reserveConfirmService->findByConfirmNumberForReserve($confirmNumber, $reserve, $itineraryNumber);
            } else {
                abort(404);
            }
        } else {
            abort(404);
        }


        if (!$reserveConfirm) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = \Gate::inspect('update', $reserveConfirm);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $agencyId = auth('staff')->user()->agency_id;
        
        $input = $request->validated();

        try {
            $reserveConfirm = \DB::transaction(function () use ($reserveConfirm, $input) {
                return $this->reserveConfirmService->update($reserveConfirm->id, $input);
            });

            if ($reserveConfirm) {
                if (request()->input("create_pdf")) { // PDF作成
                    $viewPath = '';
                    // 受付種別で分ける
                    if ($reception === config('consts.const.RECEPTION_TYPE_ASP')) { // ASP受付
                        $viewPath = 'staff.reserve_confirm.pdf';
                    } elseif ($reception === config('consts.const.RECEPTION_TYPE_WEB')) { // WEB受付
                        $viewPath = 'staff.web.reserve_confirm.pdf';
                    }
                    
                    $pdfFile = $this->reserveConfirmService->createPdf($viewPath, ['reserveConfirm' => $reserveConfirm]);

                    // 作成したPDFファイル名をセット
                    $this->reserveConfirmService->setPdf($reserveConfirm, $pdfFile, $agencyId);
                }
                if (request()->input("set_message")) {
                    request()->session()->flash('success_message', "「帳票({$reserveConfirm->confirm_number})の更新処理が完了しました。"); // set_messageは処理成功のフラッシュメッセージのセットを要求するパラメータ
                }
                return new UpdateResource($this->reserveConfirmService->find($reserveConfirm->id), 200);
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー（保存とpdf出力を同時に行う場所があるので、保存時した内容とpdfの内容が一致していることを担保する意味でもチェック）
            abort(409, "他のユーザーによる編集済みレコードです。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        } catch (\Exception $e) {
            // パラメータエラー等
            \Log::error($e);
        }
        abort(500);
    }

    /**
     * 一件削除
     *
     * @param string $agencyAccount 会社アカウント
     * @param string $applicationStep 申込段階（見積or予約）
     * @param string $controlNumber 管理番号（見積番号or予約番号）
     * @param string $itineraryNumber 行程番号
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, string $agencyAccount, string $reception, string $applicationStep, string $controlNumber, string $itineraryNumber, string $confirmNumber)
    {
        // 受付種別で分ける
        if ($reception === config('consts.const.RECEPTION_TYPE_ASP')) { // ASP受付
        // 見積or予約で処理を分ける
            if ($applicationStep == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積
                $reserve = $this->estimateService->findByEstimateNumber($controlNumber, $agencyAccount);

                $reserveConfirm = $this->reserveConfirmService->findByConfirmNumberForReserve($confirmNumber, $reserve, $itineraryNumber);
            } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約
                $reserve = $this->reserveService->findByControlNumber($controlNumber, $agencyAccount);

                $reserveConfirm = $this->reserveConfirmService->findByConfirmNumberForReserve($confirmNumber, $reserve, $itineraryNumber);
            } else {
                abort(404);
            }
        } elseif ($reception === config('consts.const.RECEPTION_TYPE_WEB')) { // WEB受付
            if ($applicationStep == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積
                $reserve = $this->webEstimateService->findByEstimateNumber($controlNumber, $agencyAccount);

                $reserveConfirm = $this->reserveConfirmService->findByConfirmNumberForReserve($confirmNumber, $reserve, $itineraryNumber);
            } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約
                $reserve = $this->webReserveService->findByControlNumber($controlNumber, $agencyAccount);

                $reserveConfirm = $this->reserveConfirmService->findByConfirmNumberForReserve($confirmNumber, $reserve, $itineraryNumber);
            } else {
                abort(404);
            }
        } else {
            abort(404);
        }

        if (!$reserveConfirm) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = \Gate::inspect('delete', [$reserveConfirm]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        if ($this->reserveConfirmService->delete($reserveConfirm->id, true)) { // 論理削除
            return response('', 200);
        }
        abort(500);
    }
}
