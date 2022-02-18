<?php

namespace App\Http\Controllers\Staff\Api;

// use App\Events\ReserveUpdateStatusEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\ReserveStatusUpdateRequest;
use App\Http\Resources\Staff\Reserve\IndexResource;
use App\Http\Resources\Staff\Reserve\ShowResource;
use App\Http\Resources\Staff\Reserve\StatusResource;
use App\Http\Resources\Staff\Reserve\VAreaResource;
use App\Models\Reserve;
use App\Services\BusinessUserManagerService;
use App\Services\ReserveCustomValueService;
use App\Services\ReserveService;
use App\Services\UserCustomItemService;
use App\Services\UserService;
use App\Services\VAreaService;
use DB;
use Exception;
use Gate;
use Illuminate\Http\Request;
use Log;

class ReserveController extends Controller
{
    public function __construct(UserService $userService, BusinessUserManagerService $businessUserManagerService, VAreaService $vAreaService, ReserveService $reserveService, ReserveCustomValueService $reserveCustomValueService, UserCustomItemService $userCustomItemService)
    {
        $this->reserveService = $reserveService;
        $this->userService = $userService;
        $this->businessUserManagerService = $businessUserManagerService;
        $this->vAreaService = $vAreaService;
        $this->reserveCustomValueService = $reserveCustomValueService;
        $this->userCustomItemService = $userCustomItemService;
    }

    // 一件取得
    public function show($agencyAccount, $reserveNumber)
    {
        $reserve = $this->reserveService->findByControlNumber($reserveNumber, $agencyAccount);

        if (!$reserve) {
            return response("データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。", 404);
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
            return response("データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。", 404);
        }

        $response = Gate::authorize('update', $reserve);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $input = $request->all();

        // ステータスのカスタム項目を取得
        $customStatus =$this->userCustomItemService->findByCodeForAgency($reserve->agency_id, config('consts.user_custom_items.CODE_APPLICATION_RESERVE_STATUS'), ['key'], null);

        if ($customStatus) {
            $this->reserveCustomValueService->upsertCustomFileds([$customStatus->key => $input['status']], $reserve->id); // カスタムフィールド保存

            // // ステータス更新イベント Web受付用なので不要
            // event(new ReserveUpdateStatusEvent($this->reserveService->find($reserve->id)));

            return response('', 200);
        }

        return abort(500);
    }

    /**
     * キャンセル
     *
     * @param string $reserveNumber 予約番号
     */
    public function cancel($agencyAccount, $reserveNumber)
    {
        $reserve = $this->reserveService->findByControlNumber($reserveNumber, $agencyAccount);

        if (!$reserve) {
            return response("データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。", 404);
        }

        // 認可チェック
        $response = Gate::inspect('update', [$reserve]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        if ($this->reserveService->cancel($reserve->id)) {
            return response('', 200);
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
            return response("データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。", 404);
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
