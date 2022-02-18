<?php

namespace App\Http\Controllers\Staff\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Staff\SubjectHotel\IndexResource;
use App\Models\SubjectHotel;
use App\Services\SubjectHotelService;
use Gate;
use Hashids;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Log;

class SubjectHotelController extends Controller
{
    public function __construct(SubjectHotelService $subjectHotelService)
    {
        $this->subjectHotelService = $subjectHotelService;
    }

    public function index($agencyAccount)
    {
        // 認可チェック
        $response = Gate::authorize('viewAny', new SubjectHotel);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        // 一応検索に使用するパラメータだけに絞る
        $params = [];
        foreach (request()->all() as $key => $val) {
            if (in_array($key, ['code','hotel_name','supplier_id']) || strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_PREFIX')) === 0) { // カスタム項目はプレフィックスを元に抽出
                $params[$key] = $val;
            }
        }

        $limit = request()->get("per_page", 10);

        return IndexResource::collection($this->subjectHotelService->paginateByAgencyAccount(
            $agencyAccount,
            $params,
            $limit,
            ['city', 'supplier']
        ));
    }

    /**
     * 削除
     */
    public function destroy($agencyAccount, $id)
    {
        $decodeId = Hashids::decode($id)[0] ?? null;
        $subjectHotel = $this->subjectHotelService->find((int)$decodeId);

        if (!$subjectHotel) {
            return response("データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。", 404);
        }

        // 認可チェック
        $response = Gate::inspect('delete', [$subjectHotel]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }
        
        if ($this->subjectHotelService->delete($subjectHotel->id, true)) { // 論理削除
            return response('', 200);
        }
        abort(500);
    }
}
