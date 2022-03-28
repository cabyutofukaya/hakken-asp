<?php

namespace App\Http\Controllers\Staff\Api;

use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\UserVisaStoreRequest;
use App\Http\Requests\Staff\UserVisaUpdateRequest;
use App\Http\Resources\Staff\UserVisa\IndexResource;
use App\Models\User;
use App\Models\UserVisa;
use App\Services\UserService;
use App\Services\UserVisaService;
use Exception;
use Gate;
use Hashids;
use Illuminate\Http\Request;
use Log;

class UserVisaController extends Controller
{
    public function __construct(UserService $userService, UserVisaService $userVisaService)
    {
        $this->userService = $userService;
        $this->userVisaService = $userVisaService;
    }

    /**
     * ビザ情報を取得
     */
    public function index(string $agencyAccount, string $userNumber)
    {
        $user = $this->userService->findByUserNumber($userNumber, $agencyAccount);

        if (!$user) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('viewAny', new UserVisa);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        return IndexResource::collection($this->userVisaService->allByUserId(
            $user->id,
            ['country', 'issue_place']
        ));
    }
    
    /**
     * ビザ情報を作成
     */
    public function store(UserVisaStoreRequest $request, string $agencyAccount, string $userNumber)
    {
        // 認可チェック
        $response = Gate::inspect('create', new UserVisa);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $user = $this->userService->findByUserNumber($userNumber, $agencyAccount);

        if (!$user) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        $input = $request->all();
        $input['user_id'] = $user->id; // ユーザーIDをセット

        if ($userVisa = $this->userVisaService->create($input)) {
            return new IndexResource($userVisa, 201);
        }
        abort(500);
    }

    /**
     * 更新
     *
     * @param string $userNumber 顧客番号
     * @param string $encodeId ビザ情報ID（ハッシュID）
     */
    public function update(UserVisaUpdateRequest $request, $agencyAccount, $userNumber, $id)
    {
        $userVisa = $this->userVisaService->find($id);

        if (!$userVisa) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('update', [$userVisa]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $input = $request->all();
        try {
            if ($userVisa = $this->userVisaService->update($userVisa->id, $input)) {
                return new IndexResource($userVisa);
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
     * @param string $encodeId ビザ情報ID（ハッシュID）
     */
    public function destroy($agencyAccount, $userNumber, $id)
    {
        $userVisa = $this->userVisaService->find($id);

        if (!$userVisa) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('forceDelete', [$userVisa]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }
        
        if ($this->userVisaService->delete($userVisa->id, false)) { // 物理削除
            return response('', 200);
        }
        abort(500);
    }
}
