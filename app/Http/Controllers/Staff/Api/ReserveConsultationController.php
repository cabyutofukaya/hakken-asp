<?php

namespace App\Http\Controllers\Staff\Api;

use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\ReserveConsultationStoreRequest;
use App\Http\Requests\Staff\ReserveConsultationUpdateRequest;
use App\Http\Resources\Staff\ReserveConsultation\IndexResource;
use App\Models\User;
use App\Models\AgencyConsultation;
use App\Services\AgencyConsultationService;
use App\Services\ReserveService;
use App\Services\EstimateService;
use Exception;
use Gate;
use Hashids;
use Illuminate\Http\Request;
use Log;

class ReserveConsultationController extends Controller
{
    public function __construct(ReserveService $reserveService, AgencyConsultationService $agencyConsultationService, EstimateService $estimateService)
    {
        $this->reserveService = $reserveService;
        $this->estimateService = $estimateService;
        $this->agencyConsultationService = $agencyConsultationService;
    }

    /**
     * 相談情報を取得
     */
    public function index(string $agencyAccount, string $applicationStep, string $controlNumber)
    {
        // 認可チェック
        $response = Gate::inspect('viewAny', new AgencyConsultation);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        // 見積or予約で処理を分ける
        if ($applicationStep == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積
            $reserve = $this->estimateService->findByEstimateNumber($controlNumber, $agencyAccount);
        } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約
            $reserve = $this->reserveService->findByControlNumber($controlNumber, $agencyAccount);
        } else {
            abort(404);
        }

        if (!$reserve) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        // 一応検索に使用するパラメータだけに絞る
        $params = [];
        foreach (request()->all() as $key => $val) {
            if (in_array($key, ['control_number']) || strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_PREFIX')) === 0) { // カスタム項目はプレフィックスを元に抽出
                $params[$key] = $val;
            }
        }

        return IndexResource::collection($this->agencyConsultationService->paginateByAgencyAccount(
            $agencyAccount,
            array_merge($params, [
                'taxonomy' => config('consts.agency_consultations.TAXONOMY_RESERVE'),
                'reserve_id' => $reserve->id
            ]),
            request()->get("per_page", 5),
            ['manager','v_agency_consultation_custom_values']
        ))->additional(['badge' => [
            'incomplete_count' => $this->agencyConsultationService->getIncompleteCount(config('consts.agency_consultations.TAXONOMY_RESERVE'), $reserve->id),
        ]]); // レスポンスに相談の未完了数を追加
    }
    
    /**
     * 相談情報を作成
     *
     * @param string $reserveNumber 予約番号
     */
    public function store(ReserveConsultationStoreRequest $request, string $agencyAccount, string $applicationStep, string $controlNumber)
    {
        // 認可チェック
        $response = Gate::inspect('create', new AgencyConsultation);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        // 見積or予約で処理を分ける
        if ($applicationStep == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積
            $reserve = $this->estimateService->findByEstimateNumber($controlNumber, $agencyAccount);
        } elseif ($applicationStep == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約
            $reserve = $this->reserveService->findByControlNumber($controlNumber, $agencyAccount);
        } else {
            abort(404);
        }


        if (!$reserve) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        $input = $request->all();

        $input['agency_id'] = auth('staff')->user()->agency_id; // 会社IDをセット
        $input['taxonomy'] = config('consts.agency_consultations.TAXONOMY_RESERVE');
        $input['reserve_id'] = $reserve->id; // 予約IDをセット

        if ($reserveConsultation = $this->agencyConsultationService->create($input)) {
            return new IndexResource($reserveConsultation, 201);
        }
        abort(500);
    }

    /**
     * 更新
     * 会社アカウントと申し込み番号以外のパラメータは特に使っていない
     *
     * @param string $applicationStep 申し込み段階
     * @param string $controlNumber 予約/見積番号
     * @param string $consulNumber 相談番号
     */
    public function update(
        ReserveConsultationUpdateRequest $request,
        string $agencyAccount,
        string $applicationStep,
        string $controlNumber,
        string $consulNumber
    ) {
        $reserveConsultation = $this->agencyConsultationService->findByControlNumber($consulNumber, $agencyAccount);

        if (!$reserveConsultation) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('update', [$reserveConsultation]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $input = $request->all();
        try {
            if ($reserveConsultation = $this->agencyConsultationService->update($reserveConsultation->id, $input)) {
                return new IndexResource($reserveConsultation);
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー
            abort(409, "他のユーザーによる編集済みレコードです。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        } catch (Exception $e) {
            Log::error($e);
        }

        abort(500);
    }

    // /**
    //  * 一件削除
    //  *
    //  * @param string $reserveNumber 予約番号
    //  * @param string $encodeId 相談情報ID（ハッシュID）
    //  */
    // public function destroy($agencyAccount, $reserveNumber, $encodeId)
    // {
    //     $decodeId = Hashids::decode($encodeId)[0] ?? null;
    //     $reserveConsultation = $this->agencyConsultationService->find((int)$decodeId);

    //     if (!$reserveConsultation) {
    //         return response("データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。", 404);
    //     }

    //     // 認可チェック
    //     $response = Gate::inspect('forceDelete', [$reserveConsultation]);
    //     if (!$response->allowed()) {
    //         abort(403, $response->message());
    //     }
        
    //     if ($this->reserveConsultationService->delete($reserveConsultation->pure_id, false)) { // 物理削除
    //         return response('', 200);
    //     }
    //     abort(500);
    // }
}
