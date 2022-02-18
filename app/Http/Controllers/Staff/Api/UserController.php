<?php

namespace App\Http\Controllers\Staff\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\UserStatusUpdateRequest;
use App\Http\Requests\Staff\UserStoretRequest;
use App\Http\Resources\Staff\User\IndexResource;
use App\Http\Resources\Staff\User\ShowResource;
use App\Http\Resources\Staff\Reserve\HistoryIndexResource;
use App\Models\User;
use App\Models\Reserve;
use App\Services\UserService;
use App\Services\ReserveEstimateService;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(UserService $userService, ReserveEstimateService $reserveEstimateService)
    {
        $this->userService = $userService;
        $this->reserveEstimateService = $reserveEstimateService;
    }

    // 一覧
    public function index($agencyAccount)
    {
        // 認可チェック
        $response = \Gate::authorize('viewAny', new User);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        // 一応検索に使用するパラメータだけに絞る
        $params = [];
        foreach (request()->all() as $key => $val) {
            if (in_array($key, ['user_number','name','name_kana','name_roman']) || strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_PREFIX')) === 0) { // カスタム項目はプレフィックスを元に抽出

                if (strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_CALENDAR_PREFIX')) === 0) { // カレンダーパラメータは日付を（YYYY/MM/DD → YYYY-MM-DD）に整形
                    $params[$key] = !is_empty($val) ? date('Y-m-d', strtotime($val)) : null;
                } else {
                    $params[$key] = $val;
                }
            }
        }
        
        $limit = request()->get("per_page", 10);

        return IndexResource::collection($this->userService->paginateByAgencyAccount(
            $agencyAccount,
            $params,
            $limit,
            ['userable.user']
        ));
    }

    /**
     * ユーザー作成
     */
    public function store(UserStoretRequest $request, $agencyAccount)
    {
        // 認可チェック
        $response = \Gate::authorize('create', new User);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $agencyId = auth('staff')->user()->agency_id;

        $input = $request->all();
        $input['agency_id'] = $agencyId; // 会社IDをセット
        $input['userable']['agency_id'] = $agencyId;

        try {
            $user = \DB::transaction(function () use ($input) {
                return $this->userService->createAspUser($input, true);
            });
            
            return new ShowResource($user);
        } catch (\Exception $e) {
            \Log::error($e);
        }
        abort(500);
    }

    /**
     * ステータス更新
     *
     * @param string $agencyAccount 会社アカウント
     * @param string $userNumber 顧客番号
     */
    public function statusUpdate(UserStatusUpdateRequest $request, $agencyAccount, $userNumber)
    {
        $user = $this->userService->findByUserNumber($userNumber, $agencyAccount);

        if (!$user || !$user->userable) {
            return response("データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。", 404);
        }

        // 認可チェック
        $response = \Gate::inspect('update', [$user]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $this->userService->updateField($user->id, ['status' => $request->input("status")]);

        return response('', 200);
    }

    /**
     * 利用履歴一覧
     */
    public function usageHistory($agencyAccount, $userNumber)
    {
        // 認可チェック
        $response = \Gate::authorize('viewAny', new User);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }
        
        $limit = request()->get("per_page", 10);

        return HistoryIndexResource::collection($this->reserveEstimateService->paginateByUserNumber(
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
        $user = $this->userService->findByUserNumber($userNumber, $agencyAccount);

        if (!$user) {
            return response("データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。", 404);
        }

        // 認可チェック
        $response = \Gate::inspect('delete', [$user]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }
        
        if ($this->userService->delete($user->id, true)) { // 論理削除
            if ($request->input("set_message")) {
                $request->session()->flash('decline_message', "「{$user->user_number}」の削除が完了しました。"); // set_messageは処理成功のフラッシュメッセージのセットを要求するパラメータ
            }
            return response('', 200);
        }
        abort(500);
    }
}
