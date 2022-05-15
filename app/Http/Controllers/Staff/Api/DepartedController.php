<?php

namespace App\Http\Controllers\Staff\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\CustomerSearchRequest;
use App\Http\Requests\Staff\EstimateStatusUpdateRequest;
use App\Http\Resources\Staff\Departed\IndexResource;
use App\Models\BusinessUserManager;
use App\Models\Reserve;
use App\Models\User;
use App\Services\BusinessUserManagerService;
use App\Services\DepartedService;
use App\Services\ReserveCustomValueService;
use App\Services\UserCustomItemService;
use App\Services\UserService;
use App\Services\VAreaService;
use Hashids;
use DB;
use Exception;
use Gate;
use Illuminate\Http\Request;
use Log;

class DepartedController extends Controller
{
    public function __construct(UserService $userService, BusinessUserManagerService $businessUserManagerService, VAreaService $vAreaService, DepartedService $departedService, ReserveCustomValueService $reserveCustomValueService, UserCustomItemService $userCustomItemService)
    {
        $this->departedService = $departedService;
        $this->userService = $userService;
        $this->businessUserManagerService = $businessUserManagerService;
        $this->vAreaService = $vAreaService;
        $this->reserveCustomValueService = $reserveCustomValueService;
        $this->userCustomItemService = $userCustomItemService;
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

        return IndexResource::collection($this->departedService->paginateByAgencyAccount(
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
     * 一件削除
     *
     * @param string $reserveNumber 予約番号
     */
    public function destroy(Request $request, string $agencyAccount, string $hashId)
    {
        $id = Hashids::decode($hashId)[0] ?? 0;

        $reserve = $this->departedService->find((int)$id);

        if (!$reserve) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('delete', [$reserve]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        if ($this->departedService->delete($reserve->id, true)) { // 論理削除

            if ($request->input("set_message")) {
                $request->session()->flash('decline_message', "{$reserveNumber}」の削除が完了しました。"); // set_messageは処理成功のフラッシュメッセージのセットを要求するパラメータ
            }

            return response('', 200);
        }
        abort(500);
    }

}
