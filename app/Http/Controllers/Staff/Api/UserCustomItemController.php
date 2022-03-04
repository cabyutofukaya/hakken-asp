<?php

namespace App\Http\Controllers\Staff\Api;

use App\Models\UserCustomItem;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\UserCustomItemService;
use App\Http\Requests\Staff\UserCustomItemToggleFlgRequest;
use App\Http\Resources\Staff\UserCustomItemResource;
use Gate;
use Log;

class UserCustomItemController extends Controller
{
    public function __construct(UserCustomItemService $userCustomItemService)
    {
        $this->userCustomItemService = $userCustomItemService;
    }

    /**
     * 有効フラグのOn ⇄ Off
     *
     * @param string $agencyAccount 会社アカウント
     */
    public function toggleFlg(UserCustomItemToggleFlgRequest $request, $agencyAccount)
    {
        $id = $request->input("id");
        $flg = (int)$request->input("flg");

        $userCustomItem = $this->userCustomItemService->find($id);

        if (!$userCustomItem) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::authorize('update', $userCustomItem);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        return $this->userCustomItemService->updateFlg($id, $flg);
    }

    /**
     * 当該カテゴリコードに紐づくカスタム項目を取得
     */
    public function getByCategoryCode($agencyAccount, $code)
    {
        // 認可チェック
        $response = Gate::authorize('viewAny', new UserCustomItem);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $agencyId = auth('staff')->user()->agency_id;

        // ユーザー管理のカスタム項目を取得
        return UserCustomItemResource::collection($this->userCustomItemService->getByCategoryCodeForAgencyAccount(
            $code, 
            auth('staff')->user()->agency->account, 
            true
        ));
    }
}
