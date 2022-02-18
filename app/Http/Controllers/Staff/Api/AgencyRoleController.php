<?php

namespace App\Http\Controllers\Staff\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Staff\AgencyRole\IndexResource;
use App\Http\Resources\Staff\AgencyRole\NamesResource;
use App\Models\AgencyRole;
use App\Services\AgencyRoleService;
use Gate;
use Log;
use Illuminate\Http\Request;

class AgencyRoleController extends Controller
{
    public function __construct(AgencyRoleService $agencyRoleService)
    {
        $this->agencyRoleService = $agencyRoleService;
    }

    public function index($agencyAccount)
    {
        // 認可チェック
        $response = Gate::authorize('viewAny', new AgencyRole);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        // 一応検索に使用するパラメータだけに絞る
        // $params = request()->only(['account','name','agency_role_id','email','status']);
        $params = [];
        $limit = request()->get("per_page", 10);

        return IndexResource::collection($this->agencyRoleService->paginateByAgencyAccount($agencyAccount, $params, $limit, [], true));
    }

    /**
     * ユーザー権限名リストを取得
     * id=>名称 形式の配列
     */
    public function names($agencyAccount)
    {
        // 認可チェック
        $response = Gate::authorize('viewAny', new AgencyRole);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $agencyId = auth('staff')->user()->agency->id;
        return ['data' => $this->agencyRoleService->getNamesByAgencyId($agencyId)];
    }
}
