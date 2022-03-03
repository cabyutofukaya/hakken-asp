<?php

namespace App\Http\Controllers\Staff\Api;

use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\UserMemberCardStoreRequest;
use App\Http\Requests\Staff\UserMemberCardUpdateRequest;
use App\Http\Resources\Staff\UserMemberCard\IndexResource;
use App\Models\User;
use App\Models\UserMemberCard;
use App\Services\UserMemberCardService;
use App\Services\UserService;
use Exception;
use Gate;
use Hashids;
use Log;
use Illuminate\Http\Request;

class UserMemberCardController extends Controller
{
    public function __construct(UserService $userService, UserMemberCardService $userMemberCardService)
    {
        $this->userService = $userService;
        $this->userMemberCardService = $userMemberCardService;
    }

    /**
     * メンバーカード情報を取得
     */
    public function index(string $agencyAccount, string $userNumber)
    {
        $user = $this->userService->findByUserNumber($userNumber, $agencyAccount);

        if (!$user) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('viewAny', new UserMemberCard);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        return IndexResource::collection($this->userMemberCardService->allByUserId(
            $user->id
        ));
    }
    
    /**
     * メンバーカード情報を作成
     */
    public function store(UserMemberCardStoreRequest $request, string $agencyAccount, string $userNumber)
    {
        // 認可チェック
        $response = Gate::inspect('create', new UserMemberCard);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        $user = $this->userService->findByUserNumber($userNumber, $agencyAccount);

        if (!$user) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        $input = $request->all();
        $input['user_id'] = $user->id; // ユーザーIDをセット

        if ($userMemberCard = $this->userMemberCardService->create($input)) {
            return new IndexResource($userMemberCard, 201);
        }
        abort(500);
    }

    /**
     * 更新
     *
     * @param string $userNumber 顧客番号
     * @param string $encodeId メンバーカード情報ID（ハッシュID）
     */
    public function update(UserMemberCardUpdateRequest $request, $agencyAccount, $userNumber, $id)
    {
        $userMemberCard = $this->userMemberCardService->find($id);

        if (!$userMemberCard) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('update', [$userMemberCard]);
        if (!$response->allowed()) {
            abort(403);
        }

        $input = $request->all();

        try {
            if ($userMemberCard = $this->userMemberCardService->update($userMemberCard->id, $input)) {
                return new IndexResource($userMemberCard);
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
     * @param string $encodeId メンバーカード情報ID（ハッシュID）
     */
    public function destroy($agencyAccount, $userNumber, $id)
    {
        $userMemberCard = $this->userMemberCardService->find($id);

        if (!$userMemberCard) {
            abort(404, "データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = Gate::inspect('forceDelete', [$userMemberCard]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }
        
        if ($this->userMemberCardService->delete($userMemberCard->id, false)) { // 物理削除
            return response('', 200);
        }
        abort(500);
    }
}
