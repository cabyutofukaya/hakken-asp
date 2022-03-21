<?php

namespace App\Http\Controllers\Staff\Api;

use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\BusinessUserConsultationStoreRequest;
use App\Http\Requests\Staff\BusinessUserConsultationUpdateRequest;
use App\Http\Resources\Staff\BusinessUserConsultation\IndexResource;
use App\Models\User;
use App\Models\AgencyConsultation;
use App\Services\AgencyConsultationService;
use App\Services\BusinessUserService;
use Exception;
use Gate;
use Hashids;
use Illuminate\Http\Request;
use Log;

class BusinessUserConsultationController extends Controller
{
    public function __construct(BusinessUserService $businessUserService, AgencyConsultationService $agencyConsultationService)
    {
        $this->businessUserService = $businessUserService;
        $this->agencyConsultationService = $agencyConsultationService;
    }

    /**
     * 相談情報を取得
     */
    public function index(string $agencyAccount, string $userNumber)
    {
        $businessUser = $this->businessUserService->findByUserNumber($userNumber, $agencyAccount);
        if (!$businessUser) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('viewAny', new AgencyConsultation);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        return IndexResource::collection($this->agencyConsultationService->paginateByAgencyAccount(
            $agencyAccount, 
            [
                'taxonomy' => config('consts.agency_consultations.TAXONOMY_BUSINESS'),
                'business_user_id' => $businessUser->id
            ], // 当該法人顧客の相談
            request()->get("per_page", 5),
            ['manager','v_agency_consultation_custom_values']
        ))->additional(['badge' => [
            'incomplete_count' => $this->agencyConsultationService->getIncompleteCount(config('consts.agency_consultations.TAXONOMY_BUSINESS'), $businessUser->id),
        ]]); // レスポンスに相談の未完了数を追加
    }
    
    /**
     * 相談情報を作成
     */
    public function store(BusinessUserConsultationStoreRequest $request, string $agencyAccount, string $userNumber)
    {
        // 認可チェック
        $response = Gate::inspect('create', new AgencyConsultation);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $businessUser = $this->businessUserService->findByUserNumber($userNumber, $agencyAccount);
        if (!$businessUser) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        $input = $request->all();

        $input['agency_id'] = auth('staff')->user()->agency_id; // 会社IDをセット
        $input['taxonomy'] = config('consts.agency_consultations.TAXONOMY_BUSINESS');
        $input['business_user_id'] = $businessUser->id; // ユーザーIDをセット

        if ($businessUserConsultation = $this->agencyConsultationService->create($input)) {
            return new IndexResource($businessUserConsultation, 201);
        }
        abort(500);
    }

    /**
     * 更新
     *
     * @param string $userNumber 顧客番号
     * @param string $encodeId 相談情報ID（ハッシュID）
     * @param string $consultationNumber 相談番号
     */
    public function update(BusinessUserConsultationUpdateRequest $request, $agencyAccount, $userNumber, $consultationNumber)
    {
        $businessUserConsultation = $this->agencyConsultationService->findByControlNumber($consultationNumber, $agencyAccount);
        if (!$businessUserConsultation) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('update', [$businessUserConsultation]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $input = $request->all();
        try {
            if ($businessUserConsultation = $this->agencyConsultationService->update($businessUserConsultation->id, $input)) {
                return new IndexResource($businessUserConsultation);
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
    //  * @param string $userNumber 顧客番号
    //  * @param string $encodeId 相談情報ID（ハッシュID）
    //  */
    // public function destroy($agencyAccount, $userNumber, $encodeId)
    // {
    //     $decodeId = Hashids::decode($encodeId)[0] ?? null;
    //     $businessUserConsultation = $this->businessUserConsultationService->find((int)$decodeId);

    //     if (!$businessUserConsultation) {
    //         return response("データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。", 404);
    //     }

    //     // 認可チェック
    //     $response = Gate::inspect('forceDelete', [$businessUserConsultation]);
    //     if (!$response->allowed()) {
    //         abort(403, $response->message());
    //     }
        
    //     if ($this->businessUserConsultationService->delete($businessUserConsultation->pure_id, false)) { // 物理削除
    //         return response('', 200);
    //     }
    //     abort(500);
    // }
}
