<?php

namespace App\Http\Controllers\Staff\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Staff\City\IndexResource;
use App\Http\Resources\Staff\City\SearchResource;
use App\Models\City;
use App\Services\CityService;
use Gate;
use Hashids;
use Illuminate\Http\Request;
use Log;


class CityController extends Controller
{
    public function __construct(CityService $cityService)
    {
        $this->cityService = $cityService;
    }

    // 一覧
    public function index($agencyAccount)
    {
        // 認可チェック
        $response = Gate::authorize('viewAny', new City);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        // 一応検索に使用するパラメータだけに絞る
        $params = [];
        foreach (request()->all() as $key => $val) {
            if (in_array($key, ['code','name','v_area_uuid'])) {
                $params[$key] = $val;
            }
        }
        
        $limit = request()->get("per_page", 10);

        return IndexResource::collection($this->cityService->paginateByAgencyAccount(
            $agencyAccount,
            $params,
            $limit,
            ['v_area']
        ));
    }

    // 一件削除
    public function destroy($agencyAccount, $encodeId)
    {
        $decodeId = Hashids::decode($encodeId)[0] ?? null;
        $city = $this->cityService->find((int)$decodeId);

        if (!$city) {
            return response("データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。", 404);
        }

        // 認可チェック
        $response = Gate::inspect('delete', [$city]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }
        
        if ($this->cityService->delete($city->id, true)) { // 論理削除
            return response('', 200);
        }
        abort(500);
    }

    /**
     * 都市・空港検索
     */
    public function search(Request $request, $agencyAccount)
    {
        return SearchResource::collection(
            $this->cityService->search(
                $agencyAccount,
                $request->city,
                [],
                ['id','code','name'],
                50
            )
        );
    }
}
