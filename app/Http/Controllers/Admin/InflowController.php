<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InflowStoreRequest;
use App\Http\Requests\Admin\InflowUpdateRequest;
use App\Http\Requests\Admin\InflowDestroyRequest;
use App\Models\Inflow;
use App\Services\InflowService;
use Gate;
use Illuminate\Http\Request;


class InflowController extends Controller
{
    const LIST_PER_PAGE = 20; // 1ページ表示件数

    private $inflowService;

    public function __construct(InflowService $inflowService)
    {
        $this->inflowService = $inflowService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('admin.inflow.index', [
            'inflows' => $this->inflowService->paginate(self::LIST_PER_PAGE),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin.inflow.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(InflowStoreRequest $request)
    {
        Gate::authorize('create', Inflow::class);

        if (($inflow = $this->inflowService->create($request->all()))) {
            return redirect()->route('admin.inflows.index', ['sort'=>'id','direction'=>'desc'])->with('success_message', "ID: {$inflow->id}「{$inflow->site_name}」を登録しました");
        }
        abort(404);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view("admin.inflow.edit", [
            'inflow' => $this->inflowService->find($id)
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(InflowUpdateRequest $request, $id)
    {
        $inflow = $this->inflowService->find((int)$id);

        // 認可チェック
        Gate::authorize('update', $inflow);

        try {
            if ($this->inflowService->update((int)$id, $request->all())) {
                return redirect()->route('admin.inflows.edit', $id)->with('success_message', "流入ID: {$id} を更新しました");
            }
        } catch(ExclusiveLockException $e) {
            return redirect()->back()->with('auth_error', '排他エラーです。');
        }

        abort(409);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(InflowDestroyRequest $request, $id)
    {
        $inflow = $this->inflowService->find((int)$id);

        // 認可チェック
        Gate::authorize('destroy', $inflow);

        if ($this->inflowService->delete((int)$id)>0) {
            return redirect()->route('admin.inflows.index', ['sort'=>'id','direction'=>'desc'])->with('success_message', "流入サイトID: {$id} 「{$inflow->site_name}」を削除しました");
        }
        abort(404); // not found
    }
}
