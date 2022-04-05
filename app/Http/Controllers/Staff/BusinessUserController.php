<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\BusinessUserStoretRequest;
use App\Http\Requests\Staff\BusinessUserUpdateRequest;
use App\Models\BusinessUser;
use App\Services\BusinessUserService;
use DB;
use Gate;
use Illuminate\Http\Request;

class BusinessUserController extends AppController
{
    public function __construct(BusinessUserService $businessUserService)
    {
        $this->businessUserService = $businessUserService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // 認可チェック
        $response = Gate::inspect('viewAny', [new BusinessUser]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view("staff.business_user.index");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // 認可チェック
        $response = Gate::inspect('create', [new BusinessUser]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view("staff.business_user.create");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(BusinessUserStoretRequest $request, $agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('create', [new BusinessUser]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $agencyId = auth('staff')->user()->agency_id;

        $input = $request->all(); // カスタムフィールドがあるので、validatedではなくallで取得

        $input['agency_id'] = $agencyId; // 会社IDをセット

        try {
            $businessUser = DB::transaction(function () use ($input) {
                return $this->businessUserService->create($input);
            });

            if ($businessUser) {
                return redirect()->route('staff.client.business.index', [$agencyAccount])->with('success_message', "「{$businessUser->user_number}」を登録しました");
            }
        } catch (Exception $e) {
            Log::error($e);
        }
        abort(500);

    }

    /**
     * Display the specified resource.
     *
     * @param  string  $userNumber 顧客番号
     * @return \Illuminate\Http\Response
     */
    public function show($agencyAccount, $userNumber)
    {
        $businessUser = $this->businessUserService->findByUserNumber($userNumber, $agencyAccount, ['prefecture:code,name','manager:id,name,status,deleted_at']);

        // 認可チェック
        $response = Gate::inspect('view', [$businessUser]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.business_user.show', compact('businessUser'));
    }

    /**
     * 編集ページ
     *
     * @param string $userNumber 顧客番号
     */
    public function edit($agencyAccount, $userNumber)
    {
        $businessUser = $this->businessUserService->findByUserNumber($userNumber, $agencyAccount, [
            'business_user_managers:id,user_number,business_user_id,name,department_name,email,tel,dm,note'
        ]);

        // 認可チェック
        $response = Gate::inspect('view', [$businessUser]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.business_user.edit', compact('businessUser'));
    }

    /**
     * 編集処理
     */
    public function update(BusinessUserUpdateRequest $request, $agencyAccount, $userNumber)
    {
        $businessUser = $this->businessUserService->findByUserNumber($userNumber, $agencyAccount);

        // 認可チェック
        $response = Gate::inspect('update', [$businessUser]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->all(); // カスタムフィールドがあるので、validatedではなくallで取得
        $agencyId = auth('staff')->user()->agency_id;

        try {
            $newBusinessUser = DB::transaction(function () use ($agencyId, $businessUser, $input) {
                return $this->businessUserService->update($agencyId, $businessUser->id, $input);
            });

            if ($newBusinessUser) {
                return redirect()->route('staff.client.business.index', [$agencyAccount])->with('success_message', "「{$newBusinessUser->user_number}」を更新しました");
            }
        } catch (ExclusiveLockException $e) { // 同時編集エラー
            return back()->withInput()->with('error_message', "他のユーザーによる編集済みレコードです。編集する前に画面を再読み込みして最新情報を表示してください。");
        } catch (Exception $e) {
            Log::error($e);
        }
        abort(500);
    }
}
