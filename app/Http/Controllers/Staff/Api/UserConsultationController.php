<?php

namespace App\Http\Controllers\Staff\Api;

use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\UserConsultationStoreRequest;
use App\Http\Requests\Staff\UserConsultationUpdateRequest;
use App\Http\Resources\Staff\UserConsultation\IndexResource;
use App\Models\User;
use App\Models\AgencyConsultation;
use App\Services\AgencyConsultationService;
use App\Services\UserService;
use Exception;
use Gate;
use Hashids;
use Illuminate\Http\Request;
use Log;

class UserConsultationController extends Controller
{
    public function __construct(UserService $userService, AgencyConsultationService $agencyConsultationService)
    {
        $this->userService = $userService;
        $this->agencyConsultationService = $agencyConsultationService;
    }

    /**
     * 相談情報を取得
     */
    public function index(string $agencyAccount, string $userNumber)
    {
        $user = $this->userService->findByUserNumber($userNumber, $agencyAccount);
        if (!$user) {
            return response("データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。", 404);
        }

        // 認可チェック
        $response = Gate::inspect('viewAny', new AgencyConsultation);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        return IndexResource::collection($this->agencyConsultationService->paginateByAgencyAccount(
            $agencyAccount,
            [
                'taxonomy' => config('consts.agency_consultations.TAXONOMY_PERSON'),
                'user_id' => $user->id
            ], // 当該個人顧客の相談
            request()->get("per_page", 5),
            ['manager','v_agency_consultation_custom_values']
        ));
    }
    
    /**
     * 相談情報を作成
     */
    public function store(UserConsultationStoreRequest $request, string $agencyAccount, string $userNumber)
    {
        // 認可チェック
        $response = Gate::inspect('create', new AgencyConsultation);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $user = $this->userService->findByUserNumber($userNumber, $agencyAccount);
        if (!$user) {
            return response("データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。", 404);
        }

        $input = $request->all();

        $input['agency_id'] = auth('staff')->user()->agency_id; // 会社IDをセット
        $input['taxonomy'] = config('consts.agency_consultations.TAXONOMY_PERSON');
        $input['user_id'] = $user->id; // ユーザーIDをセット

        if ($userConsultation = $this->agencyConsultationService->create($input)) {
            return new IndexResource($userConsultation, 201);
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
    public function update(UserConsultationUpdateRequest $request, $agencyAccount, $userNumber, $consultationNumber)
    {
        $userConsultation = $this->agencyConsultationService->findByControlNumber($consultationNumber, $agencyAccount);

        if (!$userConsultation) {
            return response("データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。", 404);
        }

        // 認可チェック
        $response = Gate::inspect('update', [$userConsultation]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $input = $request->all();
        try {
            if ($userConsultation = $this->agencyConsultationService->update($userConsultation->id, $input)) {
                return new IndexResource($userConsultation);
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー
            return response("他のユーザーによる編集済みレコードです。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。", 409);
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
    //     $userConsultation = $this->userConsultationService->find((int)$decodeId);

    //     if (!$userConsultation) {
    //         return response("データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。", 404);
    //     }

    //     // 認可チェック
    //     $response = Gate::inspect('forceDelete', [$userConsultation]);
    //     if (!$response->allowed()) {
    //         abort(403, $response->message());
    //     }
        
    //     if ($this->userConsultationService->delete($userConsultation->pure_id, false)) { // 物理削除
    //         return response('', 200);
    //     }
    //     abort(500);
    // }
}
