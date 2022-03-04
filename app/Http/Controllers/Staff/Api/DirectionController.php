<?php

namespace App\Http\Controllers\Staff\Api;

use App\Models\Direction;
use App\Models\VDirection;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\DirectionService;
use App\Services\VDirectionService;
use App\Http\Resources\Staff\VDirection\IndexResource;
use DB;
use Gate;
use Log;

class DirectionController extends Controller
{
    public function __construct(DirectionService $directionService, VDirectionService $vDirectionService)
    {
        $this->directionService = $directionService;
        $this->vDirectionService = $vDirectionService;
    }

    // 一覧
    public function index($agencyAccount)
    {
        // 認可チェック
        $response = Gate::authorize('viewAny', new VDirection);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        // 一応検索に使用するパラメータだけに絞る
        $params = [];
        foreach (request()->all() as $key => $val) {
            if (in_array($key, ['code','name'])) {
                $params[$key] = $val;
            }
        }
        
        $limit = request()->get("per_page", 10);

        // v_directionsテーブルからデータを取得
        return IndexResource::collection($this->vDirectionService->paginateByAgencyAccount(
            $agencyAccount,
            $params,
            $limit
        ));
    }

    /**
     * 1件削除
     * UUIDをPOSTで受け取りIDを指定して削除
     *
     * @param string $uuid
     */
    public function destroy($agencyAccount, $uuid)
    {
        $direction = $this->directionService->findByUuid($uuid);

        if (!$direction) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('delete', [$direction]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $result = DB::transaction(function () use ($direction) {
            return $this->directionService->delete($direction->id, true); // 論理削除
        });
        if ($result) {
            return response('', 200);
        }

        abort(500);
    }
}
