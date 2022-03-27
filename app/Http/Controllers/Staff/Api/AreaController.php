<?php

namespace App\Http\Controllers\Staff\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Staff\VArea\IndexResource;
use App\Models\Area;
use App\Services\AreaService;
use App\Services\VAreaService;
use Gate;
use Log;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function __construct(VAreaService $vAreaService, AreaService $areaService)
    {
        $this->vAreaService = $vAreaService;
        $this->areaService = $areaService;
    }

    // 一覧
    public function index($agencyAccount)
    {
        // 認可チェック
        $response = Gate::authorize('viewAny', new Area);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        // 一応検索に使用するパラメータだけに絞る
        $params = [];
        foreach (request()->all() as $key => $val) {
            if (in_array($key, ['code','name','name_en','v_direction_uuid'])) {
                $params[$key] = $val;
            }
        }
        
        $limit = request()->get("per_page", 10);

        return IndexResource::collection($this->vAreaService->paginateByAgencyAccount(
            $agencyAccount,
            $params,
            $limit,
            ['v_direction']
        ));
    }

    // 一件削除
    public function destroy($agencyAccount, $uuid)
    {
        $area = $this->areaService->findByUuid($uuid);

        if (!$area) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('delete', [$area]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }
        
        if ($this->areaService->deleteByUuid($uuid, true)) { // 論理削除
            return response('', 200);
        }
        abort(500);
    }
}
