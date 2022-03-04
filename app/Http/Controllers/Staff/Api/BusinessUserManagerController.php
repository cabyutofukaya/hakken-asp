<?php

namespace App\Http\Controllers\Staff\Api;

use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\BusinessUserManagerStoreRequest;
use App\Http\Requests\Staff\BusinessUserManagerUpdateRequest;
use App\Http\Resources\Staff\BusinessUserManager\IndexResource;
use App\Models\BusinessUser;
use App\Models\BusinessUserManager;
use App\Services\BusinessUserManagerService;
use App\Services\BusinessUserService;
use Exception;
use Gate;
use Hashids;
use Illuminate\Http\Request;
use Log;

class BusinessUserManagerController extends Controller
{
    public function __construct(BusinessUserService $busiessUserService, BusinessUserManagerService $businessUserManagerService)
    {
        $this->busiessUserService = $busiessUserService;
        $this->businessUserManagerService = $businessUserManagerService;
    }

    /**
     * 取引先担当者情報を取得
     */
    public function index(string $agencyAccount, string $userNumber)
    {
        $businessUser = $this->busiessUserService->findByUserNumber($userNumber, $agencyAccount);

        if (!$businessUser) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('viewAny', new BusinessUserManager);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        return IndexResource::collection($this->businessUserManagerService->allByUserId(
            $businessUser->id
        ));
    }
    
    /**
     * 取引先担当者情報を作成
     */
    public function store(BusinessUserManagerStoreRequest $request, string $agencyAccount, string $userNumber)
    {
        // 認可チェック
        $response = Gate::inspect('create', new BusinessUserManager);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $businessUser = $this->busiessUserService->findByUserNumber($userNumber, $agencyAccount);

        if (!$businessUser) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        $input = $request->all();
        $input['agency_id'] = auth('staff')->user()->agency_id; // 会社IDをセット
        $input['business_user_id'] = $businessUser->id; // ユーザーIDをセット

        if ($businessUserManager = $this->businessUserManagerService->create($input)) {
            return new IndexResource($businessUserManager, 201);
        }
        abort(500);
    }

    /**
     * 更新
     *
     * @param string $userNumber 顧客番号
     * @param string $id 取引先担当者ID
     */
    public function update(BusinessUserManagerUpdateRequest $request, $agencyAccount, $userNumber, $id)
    {
        $businessUserManager = $this->businessUserManagerService->find((int)$id);

        if (!$businessUserManager) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('update', [$businessUserManager]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $input = $request->all();

        try {
            if ($businessUserManager = $this->businessUserManagerService->update($businessUserManager->id, $input)) {
                return new IndexResource($businessUserManager);
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー
            abort(409, "他のユーザーによる編集済みレコードです。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        } catch (Exception $e) {
            Log::error($e);
        }
        abort(500);
    }

    /**
     * 一件削除
     *
     * @param string $userNumber 顧客番号
     * @param string $id 取引先担当者ID
     */
    public function destroy($agencyAccount, $userNumber, $id)
    {
        $businessUserManager = $this->businessUserManagerService->find((int)$id);

        if (!$businessUserManager) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('delete', [$businessUserManager]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }
        
        if ($this->businessUserManagerService->delete($businessUserManager->id, true)) { // 論理削除
            return response('', 200);
        }
        abort(500);
    }
}
