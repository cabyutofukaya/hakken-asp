<?php

namespace App\Http\Controllers\Staff\Api;

use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\AgencyConsultationStoreRequest;
use App\Http\Requests\Staff\AgencyConsultationUpdateRequest;
use App\Http\Resources\Staff\AgencyConsultation\IndexResource;
use App\Http\Resources\Staff\WebMessageHistory\IndexResource as MessageIndexResource;
use App\Models\AgencyConsultation;
use App\Models\User;
use App\Models\WebMessageHistory;
use App\Services\AgencyConsultationService;
use App\Services\EstimateService;
use App\Services\ReserveService;
use App\Services\WebMessageHistoryService;
use Exception;
use Gate;
use Illuminate\Http\Request;
use Log;

class AgencyConsultationController extends Controller
{
    public function __construct(ReserveService $reserveService, AgencyConsultationService $agencyConsultationService, EstimateService $estimateService, WebMessageHistoryService $webMessageHistoryService)
    {
        $this->reserveService = $reserveService;
        $this->estimateService = $estimateService;
        $this->agencyConsultationService = $agencyConsultationService;
        $this->webMessageHistoryService = $webMessageHistoryService;
    }

    /**
     * 相談情報を取得
     *
     * @param string $agencyAccount 会社アカウント
     */
    public function index(string $agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('viewAny', new AgencyConsultation);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        // 一応検索に使用するパラメータだけに絞る
        $params = [];
        foreach (request()->all() as $key => $val) {
            if (in_array($key, ['reserve_estimate_number','title','deadline_from','deadline_to','reception_date_from','reception_date_to','kind']) || strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_PREFIX')) === 0) { // カスタム項目はプレフィックスを元に抽出
                $params[$key] = $val;
            }
        }

        return IndexResource::collection($this->agencyConsultationService->paginateByAgencyAccount(
            $agencyAccount,
            $params,
            request()->get("per_page", 5),
            ['manager','reserve.applicantable','v_agency_consultation_custom_values']
        ));
    }

    /**
     * 更新
     *
     * @param string $agencyAccount 会社アカウント
     * @param string $id 相談ID
     */
    public function update(
        AgencyConsultationUpdateRequest $request,
        string $agencyAccount,
        int $id
    ) {
        $agencyConsultation = $this->agencyConsultationService->find($id);

        if (!$agencyConsultation) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('update', [$agencyConsultation]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $input = $request->all();
        try {
            if ($agencyConsultation = $this->agencyConsultationService->update($agencyConsultation->id, $input)) {
                return new IndexResource($agencyConsultation);
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー
            abort(409, "他のユーザーによる編集済みレコードです。編集する前に画面を再読み込みして最新情報を表示してください。");
        } catch (Exception $e) {
            Log::error($e);
        }

        abort(500);
    }

    /**
     * メッセージ履歴一覧
     *
     * @param string $agencyAccount 会社アカウント
     */
    public function messageIndex(string $agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('viewAny', new WebMessageHistory);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        // 一応検索に使用するパラメータだけに絞る
        $params = [];
        foreach (request()->all() as $key => $val) {
            if (in_array($key, ['record_number', 'message_log', 'reserve_status', 'application_date_from', 'application_date_to', 'received_at_from', 'received_at_to'])) {
                if (in_array($key, ['application_date_from','application_date_to','received_at_from','received_at_to'], true)) { // カレンダーパラメータは日付を（YYYY/MM/DD → YYYY-MM-DD）に整形
                    $params[$key] = !is_empty($val) ? date('Y-m-d', strtotime($val)) : null;
                } else {
                    $params[$key] = $val;
                }
            }
        }

        return MessageIndexResource::collection($this->webMessageHistoryService->paginateByAgencyAccount(
            $agencyAccount,
            $params,
            request()->get("per_page", 5),
            [
                'reserve.applicantable.userable',
                'reserve.manager',
                'reserve.web_reserve_ext',
                'reserve.estimate_status',
                'reserve.status',
                'reserve.application_dates',
            ]
        ));
    }

    // // /**
    // //  * 一件削除
    // //  *
    // //  * @param string $reserveNumber 予約番号
    // //  * @param string $encodeId 相談情報ID（ハッシュID）
    // //  */
    // // public function destroy($agencyAccount, $reserveNumber, $encodeId)
    // // {
    // //     $decodeId = Hashids::decode($encodeId)[0] ?? null;
    // //     $reserveConsultation = $this->reserveConsultationService->find((int)$decodeId);

    // //     if (!$reserveConsultation) {
    // //         return response("データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。", 404);
    // //     }

    // //     // 認可チェック
    // //     $response = Gate::inspect('forceDelete', [$reserveConsultation]);
    // //     if (!$response->allowed()) {
    // //         abort(403, $response->message());
    // //     }
        
    // //     if ($this->reserveConsultationService->delete($reserveConsultation->pure_id, false)) { // 物理削除
    // //         return response('', 200);
    // //     }
    // //     abort(500);
    // // }
}
