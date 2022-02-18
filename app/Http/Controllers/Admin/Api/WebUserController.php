<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Services\WebUserService;
use App\Http\Requests\Admin\WebUserStatusUpdateRequest;
use Illuminate\Http\Request;

class WebUserController extends Controller
{
    public function __construct(WebUserService $webUserService)
    {
        $this->webUserService = $webUserService;
    }

    /**
     * ステータス更新
     *
     * @param int $id ユーザーID
     */
    public function updateStatus(WebUserStatusUpdateRequest $request, int $webUserId)
    {
        $webUser = $this->webUserService->find($webUserId);

        if (!$webUser) {
            return response("データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。", 404);
        }

        // 認可チェック
        $response = \Gate::inspect('update', [$webUser]);
        if (!$response->allowed()) {
            abort(403);
        }

        $this->webUserService->updateField($webUser->id, ['status' => $request->input("status")]);

        return response('', 200);
    }

    /**
     * 一件削除
     *
     * @param int $webUserId WebユーザーID
     */
    public function destroy(Request $request, string $webUserId)
    {
        $webUser = $this->webUserService->find($webUserId);

        if (!$webUser) {
            return response("データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。", 404);
        }

        // 認可チェック
        $response = \Gate::inspect('delete', [$webUser]);
        if (!$response->allowed()) {
            abort(403);
        }
        
        if ($this->webUserService->delete($webUserId, true)) { // 論理削除
            if ($request->input("set_message")) {
                $request->session()->flash('decline_message', "「{$webUser->web_user_number}」の削除が完了しました。"); // set_messageは処理成功のフラッシュメッセージのセットを要求するパラメータ
            }
            return response('', 200);
        }
        abort(500);
    }
}
