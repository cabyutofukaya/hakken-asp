<?php

namespace App\Http\Controllers\Staff\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StaffDestroyRequest;
use App\Http\Requests\Staff\StaffIsAccountExistsRequest;
use App\Http\Requests\Staff\StaffStatusUpdateRequest;
use App\Http\Resources\Staff\Staff\IndexResource;
use App\Models\Staff;
use App\Services\StaffService;
use Gate;
use Illuminate\Http\Request;
use Log;

class StaffController extends Controller
{
    public function __construct(StaffService $staffService)
    {
        $this->staffService = $staffService;
    }

    public function index($agencyAccount)
    {
        // 認可チェック
        $response = Gate::authorize('viewAny', new Staff);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        // 一応検索に使用するパラメータだけに絞る
        $params = [];
        foreach (request()->all() as $key => $val) {
            if (in_array($key, ['account','name','agency_role_id','email','status']) || strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_PREFIX')) === 0) { // カスタム項目はプレフィックスを元に抽出
                $params[$key] = $val;
            }
        }

        $limit = request()->get("per_page", 10);

        return IndexResource::collection($this->staffService->paginateByAgencyAccount(
            $agencyAccount,
            $params,
            $limit,
            [
                'agency_role:id,name',
                'shozokus:staff_id,key,val,code', // 所属項目
            ],
            ['id','agency_id','account','name','agency_role_id','email','status']
        ));
    }

    /**
     * ステータスの更新
     *
     * @param string $agencyAccount 会社アカウント
     */
    public function statusUpdate(StaffStatusUpdateRequest $request, $agencyAccount, $account)
    {
        // 認可チェック
        $agencyId = (int)auth('staff')->user()->agency_id;
        $staff = $this->staffService->findByAccount($agencyId, $account);

        if (!$staff) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        $response = Gate::authorize('update', $staff);
        if (!$response->allowed()) {
            abort(403);
        }

        $this->staffService->updateFields($staff->id, ['status' => $request->input("status")]);

        return response('', 200);
    }

    /**
     * アカウント削除処理
     *
     * @param int $agencyAccount 会社アカウント
     * @param int $account スタッフアカウント
     */
    public function destroy(StaffDestroyRequest $request, $agencyAccount, $account)
    {
        // 認可チェック
        $agencyId = (int)auth('staff')->user()->agency_id;
        $staff = $this->staffService->findByAccount($agencyId, $account);

        if (!$staff) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        $response = Gate::authorize('delete', $staff);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        if ($this->staffService->delete($agencyId, $staff->id) > 0) {
            if ($request->input("set_message")) {
                $request->session()->flash('decline_message', "「{$staff->account}」の削除が完了しました。"); // set_messageは処理成功のフラッシュメッセージのセットを要求するパラメータ
            }
            return response('', 200);
        }
        abort(404); // not found
    }

    /**
     * アカウントが重複しているか調べる
     *
     * @param string $agencyAccount 会社アカウント
     * @return \Illuminate\Http\Response
     */
    public function isAccountExists(StaffIsAccountExistsRequest $request, $agencyAccount)
    {
        // 認可チェック
        $response = Gate::authorize('viewAny', new Staff);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        return response('', 200);
    }
}
