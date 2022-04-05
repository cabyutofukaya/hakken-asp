<?php

namespace App\Http\Controllers\Staff\Api;

use App\Events\ReserveUpdateStatusEvent;
use App\Events\UpdateBillingAmountEvent;
use App\Events\ReserveChangeHeadcountEvent;
use App\Events\PriceRelatedChangeEvent;
use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\ReserveStatusUpdateRequest;
use App\Http\Requests\Staff\ReserveNoCancelChargeCancelRequest;
use App\Http\Requests\Staff\ReserveCancelChargeUpdateRequest;
use App\Http\Resources\Staff\Reserve\IndexResource;
use App\Http\Resources\Staff\Reserve\ShowResource;
use App\Http\Resources\Staff\Reserve\StatusResource;
use App\Http\Resources\Staff\Reserve\VAreaResource;
use App\Models\Reserve;
use App\Services\BusinessUserManagerService;
use App\Services\ReserveCustomValueService;
use App\Services\ReserveParticipantPriceService;
use App\Services\ReserveService;
use App\Services\UserCustomItemService;
use App\Services\UserService;
use App\Services\VAreaService;
use App\Services\ReserveItineraryService;
use App\Services\ParticipantService;
use App\Services\ReserveParticipantOptionPriceService;
use App\Services\ReserveParticipantAirplanePriceService;
use App\Services\ReserveParticipantHotelPriceService;
use App\Services\AccountPayableDetailService;
use App\Traits\CancelChargeTrait;
use DB;
use Exception;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Log;

class ReserveController extends Controller
{
    use CancelChargeTrait;
    
    public function __construct(UserService $userService, BusinessUserManagerService $businessUserManagerService, VAreaService $vAreaService, ReserveService $reserveService, ReserveCustomValueService $reserveCustomValueService, UserCustomItemService $userCustomItemService, ReserveParticipantPriceService $reserveParticipantPriceService, ReserveItineraryService $reserveItineraryService, ParticipantService $participantService, ReserveParticipantOptionPriceService $reserveParticipantOptionPriceService, ReserveParticipantAirplanePriceService $reserveParticipantAirplanePriceService, ReserveParticipantHotelPriceService $reserveParticipantHotelPriceService, AccountPayableDetailService $accountPayableDetailService)
    {
        $this->reserveService = $reserveService;
        $this->userService = $userService;
        $this->businessUserManagerService = $businessUserManagerService;
        $this->vAreaService = $vAreaService;
        $this->reserveCustomValueService = $reserveCustomValueService;
        $this->userCustomItemService = $userCustomItemService;
        $this->reserveParticipantPriceService = $reserveParticipantPriceService;
        $this->reserveItineraryService = $reserveItineraryService;
        $this->participantService = $participantService;
        // トレイトで使用
        $this->reserveParticipantOptionPriceService = $reserveParticipantOptionPriceService;
        $this->reserveParticipantAirplanePriceService = $reserveParticipantAirplanePriceService;
        $this->reserveParticipantHotelPriceService = $reserveParticipantHotelPriceService;
        $this->accountPayableDetailService = $accountPayableDetailService;
    }

    // 一件取得
    public function show($agencyAccount, $reserveNumber)
    {
        $reserve = $this->reserveService->findByControlNumber($reserveNumber, $agencyAccount);

        if (!$reserve) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('view', [$reserve]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        return new ShowResource($reserve);
    }

    // 一覧
    public function index($agencyAccount)
    {
        // 認可チェック
        $response = Gate::authorize('viewAny', new Reserve);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        // 一応検索に使用するパラメータだけに絞る
        // applicant -> 申込者
        // representative -> 代表参加者
        $params = [];
        foreach (request()->all() as $key => $val) {
            if (in_array($key, ['control_number', 'departure_date', 'return_date', 'departure', 'destination', 'applicant', 'representative']) || strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_PREFIX')) === 0) { // カスタム項目はプレフィックスを元に抽出

                if (in_array($key, ['departure_date','return_date'], true) || strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_CALENDAR_PREFIX')) === 0) { // カレンダーパラメータは日付を（YYYY/MM/DD → YYYY-MM-DD）に整形
                    $params[$key] = !is_empty($val) ? date('Y-m-d', strtotime($val)) : null;
                } else {
                    $params[$key] = $val;
                }
            }
        }

        return IndexResource::collection($this->reserveService->paginateByAgencyAccount(
            $agencyAccount,
            $params,
            request()->get("per_page", 10),
            [
                'manager',
                'departure',
                'destination',
                'travel_types',
                'statuses',
                'application_dates',
                'applicantable',
                'representatives.user'
            ]
        ));
    }

    /**
     * 国・地域検索
     */
    public function vAreaSearch(Request $request, $agencyAccount)
    {
        // // 認可チェック。予約情報の閲覧権限でチェック
        // $response = Gate::authorize('viewAny', new Reserve);
        // if (!$response->allowed()) {
        //     abort(403, $response->message());
        // }
        
        return VAreaResource::collection(
            $this->vAreaService->search(
                $agencyAccount,
                $request->area,
                [],
                ['uuid','code','name'],
                50
            )
        );
    }

    /**
     * ステータス更新
     */
    public function statusUpdate(ReserveStatusUpdateRequest $request, $agencyAccount, $reserveNumber)
    {
        $reserve = $this->reserveService->findByControlNumber($reserveNumber, $agencyAccount);
        
        if (!$reserve) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        $response = Gate::authorize('updateStatus', $reserve);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        try {
            $input = $request->all();

            // ↓この判定はそれほど重要ではなさそうなので一旦外し
            // // ステータスが「キャンセル」になった後にステータス変更されると困るので同時編集チェック
            // if ($reserve->updated_at != Arr::get($input, 'updated_at')) {
            //     throw new ExclusiveLockException;
            // }

            // ステータスのカスタム項目を取得
            $customStatus =$this->userCustomItemService->findByCodeForAgency($reserve->agency_id, config('consts.user_custom_items.CODE_APPLICATION_RESERVE_STATUS'), ['key'], null);
    
            if ($customStatus) {
                $this->reserveCustomValueService->upsertCustomFileds([$customStatus->key => $input['status']], $reserve->id); // カスタムフィールド保存
    
                // ステータス更新イベント
                $newReserve = $this->reserveService->find($reserve->id);
                event(new ReserveUpdateStatusEvent($newReserve));
                
                return new StatusResource($newReserve);
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー
            abort(409, "他のユーザーによる編集済みレコードです。編集する前に画面を再読み込みして最新情報を表示してください。");
        } catch (Exception $e) {
            Log::error($e);
        }
        return abort(500);
    }

    /**
     * キャンセルチャージナシでキャンセル
     *
     * @param string $reserveNumber 予約番号
     */
    public function noCancelChargeCancel(ReserveNoCancelChargeCancelRequest $request, $agencyAccount, $reserveNumber)
    {
        $reserve = $this->reserveService->findByControlNumber($reserveNumber, $agencyAccount);

        if (!$reserve) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('cancel', [$reserve]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        try {

            // 同時編集チェック
            if ($reserve->updated_at != $request->updated_at) {
                throw new ExclusiveLockException;
            }

            $result = \DB::transaction(function () use ($reserve) {

                $this->participantService->setCancelByReserveId($reserve->id); // 当該予約の参加者を全てキャンセル

                $this->reserveService->cancel($reserve, false); // 予約レコードのキャンセルフラグをON

                $this->reserveParticipantPriceService->cancelChargeReset($reserve->enabled_reserve_itinerary->id); // 全ての仕入情報をキャンセルチャージ0円で初期化

                $this->reserveParticipantPriceService->setIsAliveCancelByReserveId($reserve->id, $reserve->enabled_reserve_itinerary->id); // 全有効仕入行に対し、is_alive_cancelフラグをONにする。

                if ($reserve->enabled_reserve_itinerary->id) {
                    $this->refreshItineraryTotalAmount($reserve->enabled_reserve_itinerary); // 有効行程の合計金額更新
                }

                event(new ReserveChangeHeadcountEvent($reserve)); // 参加者人数変更イベント

                event(new UpdateBillingAmountEvent($this->reserveService->find($reserve->id))); // 請求金額変更イベント

                /**カスタムステータスを「キャンセル」に更新 */

                // ステータスのカスタム項目を取得
                $customStatus =$this->userCustomItemService->findByCodeForAgency($reserve->agency_id, config('consts.user_custom_items.CODE_APPLICATION_RESERVE_STATUS'), ['key'], null);

                if ($customStatus) {
                    $this->reserveCustomValueService->upsertCustomFileds([$customStatus->key => config('consts.reserves.RESERVE_CANCEL_STATUS')], $reserve->id);

                    // ステータス更新イベント
                    event(new ReserveUpdateStatusEvent($this->reserveService->find($reserve->id)));
                }

                event(new PriceRelatedChangeEvent($reserve->id, date('Y-m-d H:i:s', strtotime("now +1 seconds")))); // 料金変更に関わるイベント。参加者情報を更新すると関連する行程レコードもtouchで日時が更新されてしまうので、他のレコードよりも確実に新しい日時で更新されるように1秒後の時間をセット

                return true;
            });

            if ($result) {
                if ($request->input("set_message")) {
                    $request->session()->flash('success_message', "{$reserve->control_number}」のキャンセル処理が完了しました。"); // set_messageは処理成功のフラッシュメッセージのセットを要求するパラメータ
                }

                return ['result' => 'ok'];
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー
            abort(409, "他のユーザーによる編集済みレコードです。編集する前に画面を再読み込みして最新情報を表示してください。");

        } catch (Exception $e) {
            Log::error($e);
        }
        return abort(500);
    }

    /**
     * キャンセルチャージ処理
     */
    public function cancelChargeUpdate(ReserveCancelChargeUpdateRequest $request, string $agencyAccount, string $controlNumber)
    {
        $reserve = $this->reserveService->findByControlNumber($controlNumber, $agencyAccount);

        // 認可チェック
        $response = Gate::inspect('cancel', [$reserve]);
        if (!$response->allowed()) {
            abort(403);
        }

        try {
            $input = $request->validated();

            // 同時編集チェック
            if ($reserve->updated_at != Arr::get($input, 'reserve.updated_at')) {
                throw new ExclusiveLockException;
            }

            DB::transaction(function () use ($input, $reserve) {

                // キャンセルチャージ料金を保存
                $this->setReserveCancelCharge($input);

                $this->reserveService->cancel($reserve, true);

                $this->refreshItineraryTotalAmount($reserve->enabled_reserve_itinerary); // 有効行程の合計金額更新

                event(new UpdateBillingAmountEvent($this->reserveService->find($reserve->id))); // 請求金額変更イベント

                /**カスタムステータスを「キャンセル」に更新 */

                // ステータスのカスタム項目を取得
                $customStatus =$this->userCustomItemService->findByCodeForAgency($reserve->agency_id, config('consts.user_custom_items.CODE_APPLICATION_RESERVE_STATUS'), ['key'], null);

                if ($customStatus) {
                    $this->reserveCustomValueService->upsertCustomFileds([$customStatus->key => config('consts.reserves.RESERVE_CANCEL_STATUS')], $reserve->id);

                    // ステータス更新イベント
                    event(new ReserveUpdateStatusEvent($this->reserveService->find($reserve->id)));
                }
            });

            if ($request->input("set_message")) {
                $request->session()->flash('success_message', "{$reserve->control_number}」のキャンセル処理が完了しました。"); // set_messageは処理成功のフラッシュメッセージのセットを要求するパラメータ
            }
            return ['result' => 'ok'];

        } catch (ExclusiveLockException $e) { // 同時編集エラー
            abort(409, "他のユーザーによる編集済みレコードです。編集する前に画面を再読み込みして最新情報を表示してください。");
        } catch (Exception $e) {
            Log::error($e);
        }
        abort(500);
    }

    /**
     * 一件削除
     *
     * @param string $reserveNumber 管理番号
     */
    public function destroy(Request $request, $agencyAccount, $reserveNumber)
    {
        $reserve = $this->reserveService->findByControlNumber($reserveNumber, $agencyAccount);

        if (!$reserve) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('delete', [$reserve]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        if ($this->reserveService->delete($reserve->id, true)) { // 論理削除

            if ($request->input("set_message")) {
                $request->session()->flash('decline_message', "{$reserveNumber}」の削除が完了しました。"); // set_messageは処理成功のフラッシュメッセージのセットを要求するパラメータ
            }

            return response('', 200);
        }
        abort(500);
    }
}
