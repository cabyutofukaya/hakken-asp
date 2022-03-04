<?php

namespace App\Http\Controllers\Staff\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Staff\SubjectAirplane\IndexResource;
use App\Models\SubjectAirplane;
use App\Services\SubjectAirplaneService;
use Gate;
use Hashids;
use Log;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class SubjectAirplaneController extends Controller
{
    public function __construct(SubjectAirplaneService $subjectAirplaneService)
    {
        $this->subjectAirplaneService = $subjectAirplaneService;
    }

    public function index($agencyAccount)
    {
        // 認可チェック
        $response = Gate::authorize('viewAny', new SubjectAirplane);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        // 一応検索に使用するパラメータだけに絞る
        $params = [];
        foreach (request()->all() as $key => $val) {
            if (in_array($key, ['code','name','departure_id','destination_id','supplier_id']) || strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_PREFIX')) === 0) { // カスタム項目はプレフィックスを元に抽出

                if (strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_CALENDAR_PREFIX')) === 0) { // カレンダーパラメータは日付を（YYYY/MM/DD → YYYY-MM-DD）に整形
                    $params[$key] = !is_empty($val) ? date('Y-m-d', strtotime($val)) : null;
                } else {
                    $params[$key] = $val;
                }

            }
        }

        $limit = request()->get("per_page", 10);

        return IndexResource::collection($this->subjectAirplaneService->paginateByAgencyAccount(
            $agencyAccount,
            $params,
            $limit,
            ['departure', 'destination', 'supplier']
        ));
    }

    /**
     * 削除
     */
    public function destroy($agencyAccount, $encodeId)
    {
        $decodeId = Hashids::decode($encodeId)[0] ?? null;
        $subjectAirplane = $this->subjectAirplaneService->find((int)$decodeId);

        if (!$subjectAirplane) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('delete', [$subjectAirplane]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }
        
        if ($this->subjectAirplaneService->delete($subjectAirplane->id, true)) { // 論理削除
            return response('', 200);
        }
        abort(500);
    }
}
