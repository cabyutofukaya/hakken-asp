<?php

namespace App\Http\Controllers\Staff\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\BusinessUserStatusUpdateRequest;
use App\Http\Resources\Staff\BusinessUser\IndexResource;
use App\Http\Resources\Staff\Reserve\HistoryIndexResource;
use App\Models\BusinessUser;
use App\Models\Reserve;
use App\Services\BusinessUserService;
use App\Services\ReserveEstimateService;
use Gate;
use Illuminate\Http\Request;
use Log;

class BusinessUserController extends Controller
{
    public function __construct(BusinessUserService $businessUserService, ReserveEstimateService $reserveEstimateService)
    {
        $this->businessUserService = $businessUserService;
        $this->reserveEstimateService = $reserveEstimateService;
    }

    // 一覧
    public function index($agencyAccount)
    {
        // 認可チェック
        $response = Gate::authorize('viewAny', new BusinessUser);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        // 一応検索に使用するパラメータだけに絞る
        $params = [];
        foreach (request()->all() as $key => $val) {
            if (in_array($key, ['user_number','name','tel']) || strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_PREFIX')) === 0) { // カスタム項目はプレフィックスを元に抽出
                
                if (strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_CALENDAR_PREFIX')) === 0) { // カレンダーパラメータは日付を（YYYY/MM/DD → YYYY-MM-DD）に整形
                    $params[$key] = !is_empty($val) ? date('Y-m-d', strtotime($val)) : null;
                } else {
                    $params[$key] = $val;
                }
            }
        }
        
        $limit = request()->get("per_page", 10);

        return IndexResource::collection($this->businessUserService->paginateByAgencyAccount(
            $agencyAccount,
            $params,
            $limit,
            ['prefecture','kbns']
        ));
    }

    /**
     * ステータス更新
     *
     * @param string $agencyAccount 会社アカウント
     * @param string $userNumber 顧客番号
     */
    public function statusUpdate(BusinessUserStatusUpdateRequest $request, $agencyAccount, $userNumber)
    {
        $businessUser = $this->businessUserService->findByUserNumber($userNumber, $agencyAccount);

        if (!$businessUser) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('update', [$businessUser]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $this->businessUserService->updateField($businessUser->id, ['status' => $request->input("status")]);

        return response('', 200);
    }

    /**
     * 利用履歴一覧
     * 
     * @param string $userNumber 顧客番号
     */
    public function usageHistory(string $agencyAccount, string $userNumber)
    {
        // 認可チェック
        $response = \Gate::authorize('viewAny', new BusinessUser);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }
        
        $limit = request()->get("per_page", 10);

        return HistoryIndexResource::collection($this->reserveEstimateService->paginateByBusinessUserNumber(
            $agencyAccount, 
            $userNumber, 
            [], 
            $limit
        ));
    }

    /**
     * 一件削除
     *
     * @param string $userNumber 顧客番号
     */
    public function destroy(Request $request, $agencyAccount, $userNumber)
    {
        $businessUser = $this->businessUserService->findByUserNumber($userNumber, $agencyAccount);

        if (!$businessUser) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('delete', [$businessUser]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }
        
        if ($this->businessUserService->delete($businessUser->id, true)) { // 論理削除
            if ($request->input("set_message")) {
                $request->session()->flash('decline_message', "{$businessUser->user_number}」の削除が完了しました。"); // set_messageは処理成功のフラッシュメッセージのセットを要求するパラメータ
            }

            return response('', 200);
        }
        abort(500);
    }
}
