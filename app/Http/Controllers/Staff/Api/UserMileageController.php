<?php

namespace App\Http\Controllers\Staff\Api;

use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\UserMileageStoreRequest;
use App\Http\Requests\Staff\UserMileageUpdateRequest;
use App\Http\Resources\Staff\UserMileage\IndexResource;
use App\Models\User;
use App\Models\UserMileage;
use App\Services\UserMileageService;
use App\Services\UserService;
use Exception;
use Gate;
use Hashids;
use Log;
use Illuminate\Http\Request;

class UserMileageController extends Controller
{
    public function __construct(UserService $userService, UserMileageService $userMileageService)
    {
        $this->userService = $userService;
        $this->userMileageService = $userMileageService;
    }

    /**
     * マイレージ情報を取得
     */
    public function index(string $agencyAccount, string $userNumber)
    {
        $user = $this->userService->findByUserNumber($userNumber, $agencyAccount);

        if (!$user) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('viewAny', new UserMileage);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        return IndexResource::collection($this->userMileageService->allByUserId(
            $user->id,
            ['v_user_mileage_custom_values']
        ));
    }
    
    /**
     * マイレージ情報を作成
     */
    public function store(UserMileageStoreRequest $request, string $agencyAccount, string $userNumber)
    {
        // 認可チェック
        $response = Gate::inspect('create', new UserMileage);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $user = $this->userService->findByUserNumber($userNumber, $agencyAccount);

        if (!$user) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        $input = $request->all();
        $input['user_id'] = $user->id; // ユーザーIDをセット

        if ($userMileage = $this->userMileageService->create($input)) {
            return new IndexResource($userMileage, 201);
        }
        abort(500);
    }

    /**
     * 更新
     *
     * @param string $userNumber 顧客番号
     * @param string $encodeId マイレージ情報ID（ハッシュID）
     */
    public function update(UserMileageUpdateRequest $request, $agencyAccount, $userNumber, $id)
    {
        $userMileage = $this->userMileageService->find($id);

        if (!$userMileage) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('update', [$userMileage]);
        if (!$response->allowed()) {
            abort(403);
        }

        try {
            $input = $request->all();

            if ($userMileage = $this->userMileageService->update($userMileage->id, $input)) {
                return new IndexResource($userMileage);
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー
            abort(409, "他のユーザーによる編集済みレコードです。編集する前に画面を再読み込みして最新情報を表示してください。");
        } catch (Exception $e) {
            Log::error($e);
        }

        abort(500);
    }

    /**
     * 一件削除
     *
     * @param string $userNumber 顧客番号
     * @param string $encodeId マイレージ情報ID（ハッシュID）
     */
    public function destroy($agencyAccount, $userNumber, $id)
    {
        $userMileage = $this->userMileageService->find($id);

        if (!$userMileage) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('forceDelete', [$userMileage]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }
        
        if ($this->userMileageService->delete($userMileage->id, false)) { // 物理削除
            return response('', 200);
        }
        abort(500);
    }
}
