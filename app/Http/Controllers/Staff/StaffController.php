<?php

namespace App\Http\Controllers\Staff;

use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\StaffStoretRequest;
use App\Http\Requests\Staff\StaffUpdateRequest;
use App\Models\Staff;
use App\Services\StaffService;
use App\Services\UserCustomCategoryService;
use DB;
use Gate;
use Illuminate\Http\Request;

class StaffController extends AppController
{
    public function __construct(StaffService $staffService, UserCustomCategoryService $userCustomCategoryService)
    {
        $this->staffService = $staffService;
        $this->userCustomCategoryService = $userCustomCategoryService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('viewAny', [new Staff]);
        if (!$response->allowed()) {
            // 権限設定更新後、システム管理権限がない状態でindexに転送されるとエラーになってしまうので、403には飛ばさずにhomeへ飛ばす。予約閲覧権限がなければ転送先で403
            return redirect(route('staff.asp.estimates.reserve.index', $agencyAccount));
        }

        return view("staff.staff.index");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // 認可チェック
        $response = Gate::inspect('create', [new Staff, auth('staff')->user()->agency->id]);
        if (!$response->allowed()) {
            abort(403);
        }
        
        return view("staff.staff.create");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StaffStoretRequest $request, $agencyAccount)
    {
        $agencyId = auth('staff')->user()->agency->id;
        // 認可チェック
        $response = Gate::inspect('create', [new Staff, $agencyId]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->all();
        $staff = DB::transaction(function () use ($input, $agencyId) {
            return $this->staffService->create($agencyId, $input);
        });

        if ($staff) {
            return redirect()->route('staff.system.user.index', $agencyAccount)->with('success_message', "ユーザー「{$staff->name}」を登録しました");
        }
        abort(500);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  string  $agencyAccount 会社アカウント
     * @param  string  $account スタッフアカウント
     * @return \Illuminate\Http\Response
     */
    public function edit($agencyAccount, $account)
    {
        $agencyId = (int)auth('staff')->user()->agency_id;

        $staff = $this->staffService->findByAccount($agencyId, $account, ['v_staff_custom_values:staff_id,key,val']);

        // 認可チェック
        $response = Gate::inspect('view', [$staff]);
        if (!$response->allowed()) {
            abort(403);
        }
        
        return view("staff.staff.edit", compact('staff'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StaffUpdateRequest $request, $agencyAccount, $account)
    {
        // 認可チェック
        $agencyId = (int)auth('staff')->user()->agency_id;
        $staff = $this->staffService->findByAccount($agencyId, $account);
        $response = Gate::inspect('update', [$staff]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }
        try {
            if ($this->staffService->update($agencyId, $staff->id, $request->all())) {
                return redirect()->route('staff.system.user.index', $agencyAccount)->with('success_message', "ユーザー「{$staff->name}」を更新しました");
            }
        } catch (ExclusiveLockException $e) {
            return back()->withInput()->with('error_message', "他のユーザーによる編集済みレコードです。編集する前に画面を再読み込みして最新情報を表示してください。");
        }
        abort(409);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($agencyAccount, $account)
    {
        //
    }
}
