<?php

namespace App\Http\Controllers\Staff\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Staff\SubjectOption\IndexResource;
use App\Models\SubjectOption;
use App\Services\SubjectOptionService;
use Gate;
use Hashids;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Log;

class SubjectOptionController extends Controller
{
    public function __construct(SubjectOptionService $subjectOptionService)
    {
        $this->subjectOptionService = $subjectOptionService;
    }

    public function index($agencyAccount)
    {
        // 認可チェック
        $response = Gate::authorize('viewAny', new SubjectOption);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        // 一応検索に使用するパラメータだけに絞る
        $params = [];
        foreach (request()->all() as $key => $val) {
            if (in_array($key, ['code','name','supplier_id']) || strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_PREFIX')) === 0) { // カスタム項目はプレフィックスを元に抽出

                if (strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_CALENDAR_PREFIX')) === 0) { // カレンダーパラメータは日付を（YYYY/MM/DD → YYYY-MM-DD）に整形
                    $params[$key] = !is_empty($val) ? date('Y-m-d', strtotime($val)) : null;
                } else {
                    $params[$key] = $val;
                }

            }
        }

        $limit = request()->get("per_page", 10);

        return IndexResource::collection($this->subjectOptionService->paginateByAgencyAccount(
            $agencyAccount,
            $params,
            $limit,
            [
                'kbns:subject_option_id,val', // 区分
                'city', 
                'supplier'
            ]
        ));
    }

    /**
     * 削除
     */
    public function destroy($agencyAccount, $id)
    {
        $decodeId = Hashids::decode($id)[0] ?? null;
        $subjectOption = $this->subjectOptionService->find((int)$decodeId);

        if (!$subjectOption) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('delete', [$subjectOption]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }
        
        if($this->subjectOptionService->delete($subjectOption->id, true)){ // 物理削除
            return response('', 200);
        }
        abort(500);
    }
}
