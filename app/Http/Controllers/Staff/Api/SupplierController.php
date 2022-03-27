<?php

namespace App\Http\Controllers\Staff\Api;

use App\Models\Supplier;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\SupplierService;
use App\Http\Resources\Staff\Supplier\IndexResource;
use Gate;
use Hashids;
use Log;

class SupplierController extends Controller
{
    public function __construct(SupplierService $supplierService)
    {
        $this->supplierService = $supplierService;
    }

    // 一覧
    public function index($agencyAccount)
    {
        // 認可チェック
        $response = Gate::authorize('viewAny', new Supplier);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        // 一応検索に使用するパラメータだけに絞る
        $params = [];
        foreach (request()->all() as $key => $val) {
            if (in_array($key, ['code','name']) || strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_PREFIX')) === 0) { // カスタム項目はプレフィックスを元に抽出

                if (strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_CALENDAR_PREFIX')) === 0) { // カレンダーパラメータは日付を（YYYY/MM/DD → YYYY-MM-DD）に整形
                    $params[$key] = !is_empty($val) ? date('Y-m-d', strtotime($val)) : null;
                } else {
                    $params[$key] = $val;
                }
            }
        }
        
        $limit = request()->get("per_page", 10);

        return IndexResource::collection($this->supplierService->paginateByAgencyAccount(
            $agencyAccount,
            $params,
            $limit
        ));
    }

    // 一件削除
    public function destroy($agencyAccount, $encodeId)
    {
        $decodeId = Hashids::decode($encodeId)[0] ?? null;
        $supplier = $this->supplierService->find((int)$decodeId);

        if (!$supplier) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('delete', [$supplier]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }
        
        if ($this->supplierService->delete($supplier->id, true)) { // 論理削除
            return response('', 200);
        }
        abort(500);
    }
}
