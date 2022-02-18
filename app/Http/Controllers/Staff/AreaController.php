<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\AreaStoretRequest;
use App\Http\Requests\Staff\AreaUpdateRequest;
use App\Models\Area;
use App\Services\AreaService;
use App\Services\VAreaService;
use Gate;
use Illuminate\Http\Request;

class AreaController extends AppController
{
    public function __construct(AreaService $areaService, VAreaService $vAreaService)
    {
        $this->areaService = $areaService;
        $this->vAreaService = $vAreaService;
    }

    /**
     * 一覧
     *
     * @return \Illuminate\Http\Response
     */
    public function index($agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('viewAny', [new Area]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.area.index');
    }

    /**
     * 作成form
     *
     * @return \Illuminate\Http\Response
     */
    public function create($agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('create', [new Area]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.area.create');
    }

    /**
     * 作成処理
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AreaStoretRequest $request, $agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('create', [new Area]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->validated();
        $input['agency_id'] = auth('staff')->user()->agency_id;

        if ($area = $this->areaService->create($input)) {
            return redirect()->route('staff.master.area.index', [$agencyAccount])->with('success_message', "「{$area->name}」を登録しました");
        }
        abort(500);
    }

    /**
     * 更新form
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($agencyAccount, $uuid)
    {
        $vArea = $this->vAreaService->findByUuid($uuid);
        // master_areasのレコードも取得対象とできるようにvAreaServiceを使用

        // 認可チェック（master_areasのデータは閲覧のみ許可なので、ここでは閲覧権限のみチェックする）
        $response = Gate::inspect('view', [$vArea]);
        if (!$response->allowed()) {
            abort(403);
        }
        return view('staff.area.edit', compact('vArea'));
    }

    /**
     * 更新処理
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(AreaUpdateRequest $request, $agencyAccount, $uuid)
    {
        $old = $this->vAreaService->findByUuid($uuid);

        // 認可チェック
        $response = Gate::inspect('update', [$old]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->validated();
        if ($new = $this->areaService->updateByUuid($uuid, $input)) {
            return redirect()->route('staff.master.area.index', [$agencyAccount])->with('success_message', "「{$new->name}」を更新しました");
        }
        abort(500);
    }

    /**
     * 削除
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */
    public function destroy($agencyAccount, $uuid)
    {
        $area = $this->vAreaService->findByUuid($uuid);

        // 認可チェック
        $response = Gate::inspect('delete', [$area]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }
        
        if ($this->areaService->deleteByUuid($uuid, true)) {
            return redirect()->route('staff.master.area.index', [$agencyAccount])->with('decline_message', "「{$area->name}」を削除しました");
        }
        abort(400); // Bad Request
    }
}
