<?php

namespace App\Http\Controllers\Staff;

use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\UserStoretRequest;
use App\Http\Requests\Staff\UserUpdateRequest;
use App\Models\User;
use App\Services\UserService;
use DB;
use Exception;
use Gate;
use Illuminate\Http\Request;
use Log;

class UserController extends AppController
{
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * 一覧
     */
    public function index()
    {
        // 認可チェック
        $response = Gate::inspect('viewAny', [new User]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view("staff.user.index");
    }

    /**
     * 登録
     */
    public function create()
    {
        // 認可チェック
        $response = Gate::inspect('create', [new User]);
        if (!$response->allowed()) {
            abort(403);
        }

        $userableType = 'App\Models\AspUser'; // 作成ユーザー種別はAspUser

        return view("staff.user.create", compact('userableType'));
    }

    /**
     * 登録処理
     */
    public function store(UserStoretRequest $request, $agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('create', [new User]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->all(); // カスタムフィールドがあるので、validatedではなくallで取得

        $agencyId = auth('staff')->user()->agency_id;
        $input['agency_id'] = $agencyId; // 会社IDをセット
        $input['userable']['agency_id'] = $agencyId;

        try {
            $user = \DB::transaction(function () use ($input) {
                return $this->userService->createAspUser($input, true);
            });

            if ($user) {
                return redirect()->route('staff.client.person.index', [$agencyAccount])->with('success_message', "「{$user->user_number}」を登録しました");
            }
        } catch (\Exception $e) {
            \Log::error($e);
        }
        abort(500);
    }

    /**
     * 表示ページ
     *
     * @param string $userNumber 顧客番号
     * @return \Illuminate\Http\Response
     */
    public function show($agencyAccount, $userNumber)
    {
        $user = $this->userService->findByUserNumber(
            $userNumber,
            $agencyAccount,
            [
                'userable.prefecture','userable.user_ext.manager'
            ]
            // [
            //     'userable.prefecture:code,name','userable.user_ext.manager:id,name,deleted_at','user_mileages.v_user_mileage_custom_values','user_visas','user_member_cards'
            // ]
        );

        // 認可チェック
        $response = Gate::inspect('view', [$user]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.user.show', compact('user'));
    }

    /**
     * 編集ページ
     *
     * @param string $userNumber 顧客番号
     */
    public function edit($agencyAccount, $userNumber)
    {
        $user = $this->userService->findByUserNumber(
            $userNumber,
            $agencyAccount,
            [
                'userable.prefecture','userable.user_ext.manager','user_visas:id,user_id,number,country_code,kind,issue_place_code,issue_date,expiration_date,note','user_mileages:id,user_id,card_number,note','user_mileages.v_user_mileage_custom_values','user_member_cards:id,user_id,card_name,card_number,note'
            ]
        );

        // 認可チェック
        $response = Gate::inspect('view', [$user]);
        if (!$response->allowed()) {
            abort(403);
        }

        $userableType = $user->userable_type;

        return view("staff.user.edit", compact("user", "userableType"));
    }

    /**
     * 編集処理
     */
    public function update(UserUpdateRequest $request, $agencyAccount, $userNumber)
    {
        $user = $this->userService->findByUserNumber($userNumber, $agencyAccount);

        // 認可チェック
        $response = Gate::inspect('update', [$user]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->all(); // カスタムフィールドがあるので、validatedではなくallで取得
        try {
            $user = DB::transaction(function () use ($user, $input) {
                return $this->userService->update(
                    $user->id,
                    $input,
                    $user->userable_type !== 'App\Models\WebUser'
                ); // WebUserの場合は同時編集チェックナシ
            });

            if ($user) {
                return redirect()->route('staff.client.person.index', [$agencyAccount])->with('success_message', "「{$user->user_number}」を更新しました");
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー
            return back()->withInput()->with('error_message', "他のユーザーによる編集済みレコードです。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。");
        } catch (Exception $e) {
            Log::error($e);
        }
        abort(500);
    }
}
