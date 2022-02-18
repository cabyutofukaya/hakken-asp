<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\DirectionStoretRequest;
use App\Http\Requests\Staff\DirectionUpdateRequest;
use App\Models\Direction;
use App\Models\VDirection;
use App\Services\DirectionService;
use App\Services\VDirectionService;
use DB;
use Gate;
use Hashids;
use Illuminate\Http\Request;

class DirectionController extends AppController
{
    public function __construct(DirectionService $directionService, VDirectionService $vDirectionService)
    {
        $this->directionService = $directionService;
        $this->vDirectionService = $vDirectionService;
    }

    /**
     * 一覧
     *
     * @return \Illuminate\Http\Response
     */
    public function index($agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('viewAny', [new Direction]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.direction.index');
    }

    /**
     * 作成form
     *
     * @return \Illuminate\Http\Response
     */
    public function create($agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('create', [new Direction]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.direction.create');
    }

    /**
     * 作成処理
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DirectionStoretRequest $request, $agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('create', [new Direction]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->validated();
        $input['agency_id'] = auth('staff')->user()->agency_id;
        
        if ($direction = $this->directionService->create($input)) {
            return redirect()->route('staff.master.direction.index', [$agencyAccount])->with('success_message', "「{$direction->name}」を登録しました");
        }
        abort(500);
    }

    /**
     * 更新form
     *
     * @param string $uuid UUID
     * @return \Illuminate\Http\Response
     */
    public function edit($agencyAccount, $uuid)
    {
        $vDirection = $this->vDirectionService->findByUuid($uuid); // master_directionsのレコードも取得対象とできるようにvDirectionServiceを使用

        // 認可チェック（master_directionsのデータは閲覧のみ許可なので、ここでは閲覧権限のみチェックする）
        $response = Gate::inspect('view', [$vDirection]);
        if (!$response->allowed()) {
            abort(403);
        }
        return view('staff.direction.edit', compact('vDirection'));
    }

    /**
     * 更新処理
     *
     * @param string $uuid UUID
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(DirectionUpdateRequest $request, $agencyAccount, $uuid)
    {
        $old = $this->vDirectionService->findByUuid($uuid);

        // 認可チェック
        $response = Gate::inspect('update', [$old]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->validated();
        if ($new = $this->directionService->updateByUuid($uuid, $input)) {
            return redirect()->route('staff.master.direction.index', [$agencyAccount])->with('success_message', "「{$new->name}」を更新しました");
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
        $direction = $this->vDirectionService->findByUuid($uuid);

        // 認可チェック
        $response = Gate::inspect('delete', [$direction]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }
        
        $result = DB::transaction(function () use ($uuid) {
            return $this->directionService->deleteByUuid($uuid, true); // 論理削除
        });
        if ($result) {
            return redirect()->route('staff.master.direction.index', [$agencyAccount])->with('decline_message', "「{$direction->name}」を削除しました");
        }
        abort(400); // Bad Request
    }
}
