<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\CityStoretRequest;
use App\Http\Requests\Staff\CityUpdateRequest;
use App\Models\City;
use App\Services\CityService;
use Gate;
use Hashids;
use Illuminate\Http\Request;

class CityController extends AppController
{
    public function __construct(CityService $cityService)
    {
        $this->cityService = $cityService;
    }

    /**
     * 一覧
     *
     * @return \Illuminate\Http\Response
     */
    public function index($agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('viewAny', [new City]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.city.index');
    }

    /**
     * 作成form
     *
     * @return \Illuminate\Http\Response
     */
    public function create($agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('create', [new City]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.city.create');
    }

    /**
     * 作成処理
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CityStoretRequest $request, $agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('create', [new City]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->validated();
        $input['agency_id'] = auth('staff')->user()->agency_id;

        if ($city = $this->cityService->create($input)) {
            return redirect()->route('staff.master.city.index', [$agencyAccount])->with('success_message', "「{$city->name}」を登録しました");
        }
        abort(500);
    }

    /**
     * 更新form
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($agencyAccount, string $hashId)
    {
        $id = Hashids::decode($hashId)[0] ?? null;
        $city = $this->cityService->find((int)$id);

        // 認可チェック
        $response = Gate::inspect('view', [$city]);
        if (!$response->allowed()) {
            abort(403);
        }
        return view('staff.city.edit', compact('city'));
    }

    /**
     * 更新処理
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(CityUpdateRequest $request, $agencyAccount, $id)
    {
        $decodeId = Hashids::decode($id)[0] ?? null;
        $old = $this->cityService->find((int)$decodeId);

        // 認可チェック
        $response = Gate::inspect('update', [$old]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->validated();
        if ($new = $this->cityService->update($decodeId, $input)) {
            return redirect()->route('staff.master.city.index', [$agencyAccount])->with('success_message', "「{$new->name}」を更新しました");
        }
        abort(500);
    }

    /**
     * 削除
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($agencyAccount, $id)
    {
        $decodeId = Hashids::decode($id)[0] ?? null;
        $city = $this->cityService->find((int)$decodeId);

        // 認可チェック
        $response = Gate::inspect('delete', [$city]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }
        
        if ($this->cityService->delete((int)$decodeId, true) > 0) {
            return redirect()->route('staff.master.city.index', [$agencyAccount])->with('decline_message', "「{$city->name}」を削除しました");
        }
        abort(400); // Bad Request
    }
}
