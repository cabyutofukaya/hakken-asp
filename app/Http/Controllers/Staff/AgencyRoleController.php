<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\AgencyRoleDestroyRequest;
use App\Http\Requests\Staff\AgencyRoleStoreRequest;
use App\Http\Requests\Staff\AgencyRoleUpdateRequest;
use App\Models\AgencyRole;
use App\Services\AgencyRoleService;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

/**
 * ユーザー権限管理
 */
class AgencyRoleController extends AppController
{
    public function __construct(AgencyRoleService $agencyRoleService)
    {
        $this->agencyRoleService = $agencyRoleService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $response = Gate::inspect('viewAny', [new AgencyRole]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view("staff.agency_role.index");
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // 認可チェック
        $response = Gate::inspect('create', [new AgencyRole]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view("staff.agency_role.create");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AgencyRoleStoreRequest $request, $agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('create', [new AgencyRole]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->all();
        $input['agency_id'] = auth('staff')->user()->agency->id; // 会社IDをセット
        $input['master'] = false; // マスター権限は作成できない（会社登録時にのみ作成）のでfalseで固定

        if (($agencyRole = $this->agencyRoleService->create($input))) {
            return redirect()->route('staff.system.role.index', $agencyAccount)->with('success_message', "ユーザー権限を追加しました");
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($agencyAccount, $id)
    {
        $agencyRole = $this->agencyRoleService->find((int)$id);

        // 認可チェック
        $response = Gate::inspect('view', [$agencyRole]);
        if (!$response->allowed()) {
            abort(403);
        }

        $searchParam = ['agency_role_id' => $agencyRole->id];// 検索パラメータ
        return view("staff.agency_role.edit", compact('agencyRole', 'searchParam'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(AgencyRoleUpdateRequest $request, $agencyAccount, $id)
    {
        $agencyRole = $this->agencyRoleService->find((int)$id);
        // 認可チェック
        $response = Gate::inspect('update', [$agencyRole]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }
        
        $input = $request->all();
        $input['authority'] = Arr::get($input, "authority", null); // authorityパラメータが無い場合は既存の値を削除できるようにnullで初期化

        if ($this->agencyRoleService->update((int)$id, $input)) {
            return redirect()->route('staff.system.role.index', $agencyAccount)->with('success_message', "ユーザー権限を更新しました");
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(AgencyRoleDestroyRequest $request, $agencyAccount, $id)
    {
        $agencyRole = $this->agencyRoleService->find((int)$id);
        // 認可チェック
        $response = Gate::inspect('delete', [$agencyRole]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }
        
        if ($this->agencyRoleService->delete((int)$id) > 0) {
            return redirect()->route('staff.system.role.index', $agencyAccount)->with('success_message', "ユーザー権限を削除しました");
        }
        abort(400); // Bad Request
    }
}
