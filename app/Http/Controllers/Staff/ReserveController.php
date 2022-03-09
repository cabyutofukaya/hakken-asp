<?php

namespace App\Http\Controllers\Staff;

use App\Events\ReserveChangeHeadcountEvent;
use App\Events\ReserveChangeRepresentativeEvent;
use App\Events\ReserveEvent;
use App\Events\ReserveUpdateStatusEvent;
use App\Events\UpdateBillingAmountEvent;
use App\Events\UpdatedReserveEvent;
use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\CancelChargeUpdateRequest;
use App\Http\Requests\Staff\ReserveStoretRequest;
use App\Http\Requests\Staff\ReserveUpdateRequest;
use App\Models\Reserve;
use App\Services\AccountPayableDetailService;
use App\Services\ReserveCustomValueService;
use App\Services\ReserveInvoiceService;
use App\Services\ReserveParticipantAirplanePriceService;
use App\Services\ReserveParticipantHotelPriceService;
use App\Services\ReserveParticipantOptionPriceService;
use App\Services\ReserveParticipantPriceService;
use App\Services\ReserveService;
use App\Services\UserCustomItemService;
use App\Traits\CancelChargeTrait;
use App\Traits\ReserveControllerTrait;
use DB;
use Exception;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Log;

class ReserveController extends AppController
{
    use ReserveControllerTrait,CancelChargeTrait;

    public function __construct(ReserveService $reserveService, ReserveInvoiceService $reserveInvoiceService, ReserveParticipantPriceService $reserveParticipantPriceService, ReserveParticipantOptionPriceService $reserveParticipantOptionPriceService, ReserveParticipantAirplanePriceService $reserveParticipantAirplanePriceService, ReserveParticipantHotelPriceService $reserveParticipantHotelPriceService, ReserveCustomValueService $reserveCustomValueService, UserCustomItemService $userCustomItemService, AccountPayableDetailService $accountPayableDetailService)
    {
        $this->reserveService = $reserveService;
        $this->reserveInvoiceService = $reserveInvoiceService;
        $this->reserveParticipantPriceService = $reserveParticipantPriceService;
        $this->reserveCustomValueService = $reserveCustomValueService;
        $this->reserveParticipantOptionPriceService = $reserveParticipantOptionPriceService;
        $this->reserveParticipantAirplanePriceService = $reserveParticipantAirplanePriceService;
        $this->reserveParticipantHotelPriceService = $reserveParticipantHotelPriceService;
        $this->userCustomItemService = $userCustomItemService;
        $this->accountPayableDetailService = $accountPayableDetailService;
    }

    public function index()
    {
        // 認可チェック
        $response = Gate::inspect('viewAny', [new Reserve]);
        if (!$response->allowed()) {
            abort(403);
        }
        
        return view('staff.reserve.index');
    }

    /**
     * 詳細表示ページ
     *
     * @param string $controlNumber 予約番号
     */
    public function show($agencyAccount, $controlNumber)
    {
        $reserve = $this->reserveService->findByControlNumber($controlNumber, $agencyAccount);

        // 認可チェック
        $response = Gate::inspect('view', [$reserve]);
        if (!$response->allowed()) {
            abort(403);
        }

        // 催行済みの場合は転送
        $this->checkReserveState($agencyAccount, $reserve);

        return view('staff.reserve.show', compact('reserve'));
    }

    /**
     * 新規予約作成ページ
     */
    public function create()
    {
        // 認可チェック
        $response = Gate::inspect('create', [new Reserve]);
        if (!$response->allowed()) {
            abort(403);
        }

        $applicationStep = config('consts.reserves.APPLICATION_STEP_RESERVE'); // 申込段階。CreateFormComposer内で見積と予約で処理を分ける際に使用する変数

        return view('staff.reserve.create', compact('applicationStep'));
    }

    /**
     * 新規予約作成処理
     */
    public function store(ReserveStoretRequest $request, $agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('create', [new Reserve]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $agencyId = auth('staff')->user()->agency_id;

        $input = $request->all(); // カスタムフィールドがあるので、validatedではなくallで取得

        $input['agency_id'] = $agencyId; // 会社IDをセット

        try {
            $reserve = DB::transaction(function () use ($agencyAccount, $input) {
                $reserve = $this->reserveService->create(config('consts.reserves.RECEPTION_TYPE_ASP'), $agencyAccount, $input, config('consts.reserves.APPLICATION_STEP_RESERVE'));

                event(new ReserveChangeRepresentativeEvent($reserve)); // 代表者変更イベント

                event(new ReserveChangeHeadcountEvent($reserve)); // 参加者人数変更イベント

                event(new ReserveEvent($reserve)); // 予約作成イベント

                return $reserve;
            });

            if ($reserve) {
                if ($reserve->is_departed) { // 催行済の場合は催行一覧へ
                    return redirect(route('staff.estimates.departed.index', [$agencyAccount]))->with('success_message', "「{$reserve->control_number}」を登録しました");
                } else {
                    return redirect()->route('staff.asp.estimates.reserve.index', [$agencyAccount])->with('success_message', "「{$reserve->control_number}」を登録しました");
                }
            }
        } catch (Exception $e) {
            Log::error($e);
        }
        abort(500);
    }

    /**
     * 編集ページ
     *
     * @param string $controlNumber 予約番号
     */
    public function edit(string $agencyAccount, string $controlNumber)
    {
        $reserve = $this->reserveService->findByControlNumber($controlNumber, $agencyAccount);

        // 認可チェック
        $response = Gate::inspect('view', [$reserve]);
        if (!$response->allowed()) {
            abort(403);
        }

        // 催行済みの場合は転送
        $this->checkReserveState($agencyAccount, $reserve);

        return view('staff.reserve.edit', compact('reserve'));
    }

    /**
     * 更新処理
     */
    public function update(ReserveUpdateRequest $request, string $agencyAccount, string $controlNumber)
    {
        $reserve = $this->reserveService->findByControlNumber($controlNumber, $agencyAccount);

        // 認可チェック
        $response = Gate::inspect('update', [$reserve]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        // 催行済みの場合は転送
        $this->checkReserveState($agencyAccount, $reserve);

        $input = $request->all();
        try {
            $updatedReserve = DB::transaction(function () use ($reserve, $agencyAccount, $input) {
                $updatedReserve = $this->reserveService->update($reserve->id, $agencyAccount, $input);

                event(new UpdatedReserveEvent($reserve, $updatedReserve));

                return $updatedReserve;
            });

            if ($updatedReserve) {
                if ($updatedReserve->is_departed) { // 催行済の場合は催行一覧へ
                    return redirect(route('staff.estimates.departed.index', [$agencyAccount]))->with('success_message', "「{$updatedReserve->control_number}」を更新しました");
                } else {
                    return redirect()->route('staff.asp.estimates.reserve.index', [$agencyAccount])->with('success_message', "「{$updatedReserve->control_number}」を更新しました");
                }
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー
            return back()->withInput()->with('error_message', "他のユーザーによる編集済みレコードです。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        } catch (Exception $e) {
            Log::error($e);
        }
        abort(500);
    }

    /**
     * キャンセルチャージ設定ページ
     */
    public function cancelCharge(string $agencyAccount, string $controlNumber)
    {
        $reserve = $this->reserveService->findByControlNumber($controlNumber, $agencyAccount);

        // 認可チェック
        $response = Gate::inspect('cancel', [$reserve]);
        if (!$response->allowed()) {
            abort(403);
        }

        // 念の為、予約状態であることを確認
        if ($reserve->application_step != config('consts.reserves.APPLICATION_STEP_RESERVE')) {
            abort(404);
        }

        // 支払い情報を取得
        $purchasingList = $this->getPurchasingList($reserve);

        return view('staff.reserve.cancel_charge', compact('reserve', 'purchasingList'));
    }

    /**
     * キャンセルチャージ処理
     */
    public function cancelChargeUpdate(CancelChargeUpdateRequest $request, string $agencyAccount, string $controlNumber)
    {
        $reserve = $this->reserveService->findByControlNumber($controlNumber, $agencyAccount);

        // 認可チェック
        $response = Gate::inspect('cancel', [$reserve]);
        if (!$response->allowed()) {
            abort(403);
        }

        try {

            $input = $request->validated();

            DB::transaction(function () use ($input, $reserve) {

                // キャンセルチャージ料金を保存
                $this->setCancelCharge($input);
                
                $this->reserveService->cancel($reserve->id, true, Arr::get($input, 'reserve.updated_at'));

                event(new UpdateBillingAmountEvent($reserve->enabled_reserve_itinerary)); // 請求金額変更イベント

                /**カスタムステータスを「キャンセル」に更新 */

                // ステータスのカスタム項目を取得
                $customStatus =$this->userCustomItemService->findByCodeForAgency($reserve->agency_id, config('consts.user_custom_items.CODE_APPLICATION_RESERVE_STATUS'), ['key'], null);

                if ($customStatus) {
                    $this->reserveCustomValueService->upsertCustomFileds([$customStatus->key => config('consts.reserves.RESERVE_CANCEL_STATUS')], $reserve->id);

                    // ステータス更新イベント
                    event(new ReserveUpdateStatusEvent($this->reserveService->find($reserve->id)));
                }
            });

            // 予約詳細ページへリダイレクト
            return redirect()->route('staff.asp.estimates.reserve.show', [$agencyAccount, $controlNumber])->with('success_message', "「{$controlNumber}」のキャンセルチャージ処理が完了しました");

        } catch (ExclusiveLockException $e) { // 同時編集エラー
            return back()->withInput()->with('error_message', "他のユーザーによる編集済みレコードです。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
            
        } catch (Exception $e) {
            Log::error($e);
        }
        abort(500);

    }
}
