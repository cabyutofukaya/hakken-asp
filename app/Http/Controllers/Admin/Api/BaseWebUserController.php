<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Services\BaseWebUserService;
use App\Http\Requests\Admin\WebUserStatusUpdateRequest;
use Illuminate\Http\Request;

class BaseWebUserController extends Controller
{
    public function __construct(BaseWebUserService $baseWebUserService)
    {
        $this->baseWebUserService = $baseWebUserService;
    }

    /**
     * ステータス更新
     *
     * @param int $id ユーザーID
     */
    public function updateStatus(WebUserStatusUpdateRequest $request, int $webUserId)
    {
        $webUser = $this->baseWebUserService->find($webUserId);

        if (!$webUser) {
            return response("データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。", 404);
        }

        // 認可チェック
        $response = \Gate::inspect('update', [$webUser]);
        if (!$response->allowed()) {
            abort(403);
        }

        $this->baseWebUserService->updateField($webUser->id, ['status' => $request->input("status")]);

        return response('', 200);
    }

    /**
     * 一件削除
     *
     * @param int $webUserId WebユーザーID
     */
    public function destroy(Request $request, string $webUserId)
    {
        $webUser = $this->baseWebUserService->find($webUserId);

        if (!$webUser) {
            return response("データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。", 404);
        }

        // 認可チェック
        $response = \Gate::inspect('delete', [$webUser]);
        if (!$response->allowed()) {
            abort(403);
        }
        
        if ($this->baseWebUserService->delete($webUserId, true)) { // 論理削除
            if ($request->input("set_message")) {
                $request->session()->flash('decline_message', "「{$webUser->web_user_number}」の削除が完了しました。"); // set_messageは処理成功のフラッシュメッセージのセットを要求するパラメータ
            }
            return response('', 200);
        }
        abort(500);
    }
}
