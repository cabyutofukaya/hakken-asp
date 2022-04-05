<?php

namespace App\Http\Controllers\Staff\Web;

use App\Events\ReserveUpdateStatusEvent;
use App\Events\UpdateBillingAmountEvent;
use App\Events\UpdatedReserveEvent;
use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Staff\AppController;
use App\Http\Requests\Staff\ReserveUpdateRequest;
use App\Models\Reserve;
use App\Services\AccountPayableDetailService;
use App\Services\ReserveCustomValueService;
use App\Services\ReserveInvoiceService;
use App\Services\ReserveParticipantAirplanePriceService;
use App\Services\ReserveParticipantHotelPriceService;
use App\Services\ReserveParticipantOptionPriceService;
use App\Services\ReserveParticipantPriceService;
use App\Services\UserCustomItemService;
use App\Services\WebReserveService;
use App\Services\ReserveItineraryService;
use App\Traits\CancelChargeTrait;
use App\Traits\ReserveControllerTrait;
use App\Traits\ReserveItineraryTrait;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
 * 予約管理
 */
class ReserveController extends AppController
{
    use ReserveControllerTrait, CancelChargeTrait, ReserveItineraryTrait;

    public function __construct(WebReserveService $webReserveService, ReserveInvoiceService $reserveInvoiceService, ReserveParticipantPriceService $reserveParticipantPriceService, ReserveParticipantOptionPriceService $reserveParticipantOptionPriceService, ReserveParticipantHotelPriceService $reserveParticipantHotelPriceService, ReserveParticipantAirplanePriceService $reserveParticipantAirplanePriceService, ReserveCustomValueService $reserveCustomValueService, UserCustomItemService $userCustomItemService, AccountPayableDetailService $accountPayableDetailService,ReserveItineraryService $reserveItineraryService)
    {
        $this->webReserveService = $webReserveService;
        $this->reserveInvoiceService = $reserveInvoiceService;
        $this->reserveParticipantPriceService = $reserveParticipantPriceService;
        $this->reserveCustomValueService = $reserveCustomValueService;
        $this->userCustomItemService = $userCustomItemService;
        // traitで使用
        $this->reserveParticipantOptionPriceService = $reserveParticipantOptionPriceService;
        $this->reserveParticipantHotelPriceService = $reserveParticipantHotelPriceService;
        $this->reserveParticipantAirplanePriceService = $reserveParticipantAirplanePriceService;
        $this->accountPayableDetailService = $accountPayableDetailService;
        $this->reserveItineraryService = $reserveItineraryService;
    }

    /**
     * 予約一覧ページ
     */
    public function index()
    {
        // 認可チェック
        $response = Gate::inspect('viewAny', [new Reserve]);
        if (!$response->allowed()) {
            abort(403);
        }
        
        return view('staff.web.reserve.index');
    }

    /**
     * 詳細表示ページ
     *
     * @param string $controlNumber 予約番号
     */
    public function show($agencyAccount, $controlNumber)
    {
        $reserve = $this->webReserveService->findByControlNumber($controlNumber, $agencyAccount);

        // 認可チェック
        $response = Gate::inspect('view', [$reserve]);
        if (!$response->allowed()) {
            abort(403);
        }

        // 催行済みの場合は転送
        $this->checkReserveState($agencyAccount, $reserve);

        return view('staff.web.reserve.show', compact('reserve'));
    }

    /**
     * 編集ページ
     *
     * @param string $controlNumber 予約番号
     */
    public function edit(string $agencyAccount, string $controlNumber)
    {
        $reserve = $this->webReserveService->findByControlNumber($controlNumber, $agencyAccount);

        // 認可チェック
        $response = Gate::inspect('view', [$reserve]);
        if (!$response->allowed()) {
            abort(403);
        }

        // 催行済みの場合は転送
        $this->checkReserveState($agencyAccount, $reserve);

        return view('staff.web.reserve.edit', compact('reserve'));
    }

    /**
     * 更新処理
     */
    public function update(ReserveUpdateRequest $request, string $agencyAccount, string $controlNumber)
    {
        $reserve = $this->webReserveService->findByControlNumber($controlNumber, $agencyAccount);

        // 認可チェック
        $response = Gate::inspect('update', [$reserve]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->all();
        try {
            $updatedReserve = \DB::transaction(function () use ($reserve, $agencyAccount, $input) {
                $updatedReserve = $this->webReserveService->update($reserve->id, $agencyAccount, $input);

                event(new UpdatedReserveEvent($reserve, $updatedReserve));

                return $updatedReserve;
            });

            if ($updatedReserve) {
                if ($updatedReserve->is_departed) { // 催行済の場合は催行一覧へ
                    return redirect(route('staff.estimates.departed.index', [$agencyAccount]))->with('success_message', "「{$updatedReserve->control_number}」を更新しました");
                } else {
                    if (!$updatedReserve->is_canceled && $updatedReserve->reserve_itinerary_exists && ($reserve->departure_date != $updatedReserve->departure_date || $reserve->return_date != $updatedReserve->return_date)) { // 行程が登録されていて旅行日が変わった場合はメッセージを変える
                        $successMessage = "「{$updatedReserve->control_number}」を更新しました。旅行日が変更されている場合は行程の更新も行ってください";
                    } else {
                        $successMessage = "「{$updatedReserve->control_number}」を更新しました";
                    }
                    return redirect()->route('staff.web.estimates.reserve.index', [$agencyAccount])->with('success_message', $successMessage);
                }
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー
            return back()->withInput()->with('error_message', "他のユーザーによる編集済みレコードです。編集する前に画面を再読み込みして最新情報を表示してください。");
        } catch (\Exception $e) {
            \Log::error($e);
        }
        abort(500);
    }

    /**
     * キャンセルチャージ設定ページ
     */
    public function cancelCharge(string $agencyAccount, string $controlNumber)
    {
        $reserve = $this->webReserveService->findByControlNumber($controlNumber, $agencyAccount);

        // 認可チェック
        $response = Gate::inspect('cancel', [$reserve]);
        if (!$response->allowed()) {
            abort(403);
        }

        // 念の為、予約状態であることを確認
        if ($reserve->application_step != config('consts.reserves.APPLICATION_STEP_RESERVE')) {
            abort(404);
        }

        // 参加者情報
        $participants = array();
        foreach ($reserve->participants as $participant) {
            $participants[$participant->id] = $this->getPaticipantRow($participant);
        }

        // 支払い情報を取得。有効仕入(valid=true)のみ取得
        $purchasingList = $this->getPurchasingListByReserve($reserve, $participants, true);

        return view('staff.web.reserve.cancel_charge', compact('reserve', 'purchasingList'));
    }
}
