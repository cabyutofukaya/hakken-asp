<?php

namespace App\Http\Controllers\Staff\Api\Web;

use App\Events\ReserveUpdateStatusEvent;
use App\Events\UpdateBillingAmountEvent;
use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\ReserveStatusUpdateRequest;
use App\Http\Resources\Staff\WebReserve\IndexResource;
use App\Http\Resources\Staff\WebReserve\ShowResource;
use App\Http\Resources\Staff\WebReserve\StatusResource;
use App\Http\Resources\Staff\WebReserve\VAreaResource;
use App\Models\Reserve;
use App\Services\BusinessUserManagerService;
use App\Services\ReserveCustomValueService;
use App\Services\ReserveParticipantPriceService;
use App\Services\UserCustomItemService;
use App\Services\UserService;
use App\Services\VAreaService;
use App\Services\WebReserveService;
use DB;
use Exception;
use Gate;
use Hashids;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Log;

class ReserveController extends Controller
{
    public function __construct(UserService $userService, BusinessUserManagerService $businessUserManagerService, VAreaService $vAreaService, WebReserveService $webReserveService, ReserveCustomValueService $reserveCustomValueService, UserCustomItemService $userCustomItemService, ReserveParticipantPriceService $reserveParticipantPriceService)
    {
        $this->webReserveService = $webReserveService;
        $this->userService = $userService;
        $this->businessUserManagerService = $businessUserManagerService;
        $this->vAreaService = $vAreaService;
        $this->reserveCustomValueService = $reserveCustomValueService;
        $this->userCustomItemService = $userCustomItemService;
        $this->reserveParticipantPriceService = $reserveParticipantPriceService;
    }

    // 一件取得
    public function show($agencyAccount, $reserveNumber)
    {
        $reserve = $this->webReserveService->findByControlNumber($reserveNumber, $agencyAccount);

        if (!$reserve) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
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

        return IndexResource::collection($this->webReserveService->paginateByAgencyAccount(
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
        // 認可チェック。予約情報の閲覧権限でチェック
        $response = Gate::authorize('viewAny', new Reserve);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }
        
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
        $reserve = $this->webReserveService->findByControlNumber($reserveNumber, $agencyAccount);

        if (!$reserve) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        $response = Gate::authorize('update', $reserve);
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
            $customStatus = $this->userCustomItemService->findByCodeForAgency($reserve->agency_id, config('consts.user_custom_items.CODE_APPLICATION_RESERVE_STATUS'), ['key'], null);
    
            if ($customStatus) {
                $this->reserveCustomValueService->upsertCustomFileds([$customStatus->key => $input['status']], $reserve->id); // カスタムフィールド保存
    
                // ステータス更新イベント
                $newReserve = $this->webReserveService->find($reserve->id);
                event(new ReserveUpdateStatusEvent($newReserve));
                
                return new StatusResource($newReserve);
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー
            abort(409, "他のユーザーによる編集済みレコードです。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
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
    public function noCancelChargeCancel(Request $request, $agencyAccount, $reserveNumber)
    {
        $reserve = $this->webReserveService->findByControlNumber($reserveNumber, $agencyAccount);

        if (!$reserve) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('cancel', [$reserve]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        try {
            $result = \DB::transaction(function () use ($reserve) {
                $this->reserveParticipantPriceService->cancelChargeReset($reserve->enabled_reserve_itinerary->id); // キャンセルチャージをリセット
                $this->webReserveService->cancel($reserve->id, false, null);

                event(new UpdateBillingAmountEvent($this->webReserveService->find($reserve->id))); // 請求金額変更イベント

                /**カスタムステータスを「キャンセル」に更新 */

                // ステータスのカスタム項目を取得
                $customStatus =$this->userCustomItemService->findByCodeForAgency($reserve->agency_id, config('consts.user_custom_items.CODE_APPLICATION_RESERVE_STATUS'), ['key'], null);

                if ($customStatus) {
                    $this->reserveCustomValueService->upsertCustomFileds([$customStatus->key => config('consts.reserves.RESERVE_CANCEL_STATUS')], $reserve->id);

                    // ステータス更新イベント
                    event(new ReserveUpdateStatusEvent($this->webReserveService->find($reserve->id)));
                }

                return true;
            });

            if ($result) {
                if ($request->input("set_message")) {
                    $request->session()->flash('success_message', "{$reserve->control_number}」のキャンセル処理が完了しました。"); // set_messageは処理成功のフラッシュメッセージのセットを要求するパラメータ
                }

                return response('', 200);
            }
        } catch (Exception $e) {
            Log::error($e);
        }
        return abort(500);
    }

    /**
     * 一件削除
     *
     * @param string $reserveNumber 管理番号
     */
    public function destroy(Request $request, $agencyAccount, string $hashId)
    {
        $id = Hashids::decode($hashId)[0] ?? 0;
        $reserve = $this->webReserveService->find((int)$id);

        if (!$reserve) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('delete', [$reserve]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        if ($this->webReserveService->delete($reserve->id, true)) { // 論理削除

            if ($request->input("set_message")) {
                $request->session()->flash('decline_message', "{$reserve->control_number}」の削除が完了しました。"); // set_messageは処理成功のフラッシュメッセージのセットを要求するパラメータ
            }

            return response('', 200);
        }
        abort(500);
    }
}
