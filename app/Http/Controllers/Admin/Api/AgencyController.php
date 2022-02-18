<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AgencyIsAccountExistsRequest;
use App\Http\Requests\Admin\AgencyUpdateStatusRequest;
use App\Models\Agency;
use App\Services\AgencyService;
use Gate;
use Illuminate\Http\Request;

class AgencyController extends Controller
{
    private $agencyService;

    public function __construct(AgencyService $agencyService)
    {
        $this->agencyService = $agencyService;
    }

    /**
     * 名前検索
     */
    public function selectSearchCompanyNameApi(Request $request)
    {
        $name = $request->name;
        $exclusionId = $request->exclusion_id ? (int)$request->exclusion_id : null;
        return $this->agencyService->selectSearchCompanyName($name, $exclusionId, 100);
    }

    /**
     * アカウントの重複有無を調べる
     *
     * @return \Illuminate\Http\Response
     */
    public function isAccountExistsApi(AgencyIsAccountExistsRequest $request)
    {
        // 認可チェック
        $response = Gate::authorize('isAccountExists', Agency::class);
        if (!$response->allowed()) {
            abort(403);
        }

        return ["exists" => 'no'];
    }

    /**
     * ステータス更新
     *
     * @param int $id 会社ID
     */
    public function updateStatus(AgencyUpdateStatusRequest $request, int $id)
    {
        $agency = $this->agencyService->find($id);

        if (!$agency) {
            return response("データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。", 404);
        }

        // 認可チェック
        $response = \Gate::inspect('updateStatus', [$agency]);
        if (!$response->allowed()) {
            abort(403);
        }

        $this->agencyService->updateField($id, ['status' => $request->input("status")]);

        return response('', 200);
    }

    /**
     * 削除
     *
     * @param int $id 会社ID
     */
    public function destroy(Request $request, int $id)
    {
        $agency = $this->agencyService->find($id);

        if (!$agency) {
            return response("データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。", 404);
        }

        // 認可チェック
        $response = Gate::inspect('delete', [$agency]);
        if (!$response->allowed()) {
            abort(403);
        }
        
        if ($this->agencyService->delete($id, true)) { // 論理削除

            if ($request->input("set_message")) {
                $request->session()->flash('decline_message', "会社ID:{$id}の削除処理が完了しました。"); // set_messageは処理成功のフラッシュメッセージのセットを要求するパラメータ
            }

            return response('', 200);
        }
        abort(500);
    }
}
