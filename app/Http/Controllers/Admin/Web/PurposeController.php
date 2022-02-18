<?php
declare(strict_types=1);

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PurposeStoreRequest;
use App\Http\Requests\Admin\PurposeUpdateRequest;
use App\Http\Requests\Admin\PurposeDestroyRequest;
use App\Services\Hakken\PurposeService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurposeController extends Controller
{
    private $purposeService;

    public function __construct(PurposeService $purposeService)
    {
        $this->purposeService = $purposeService;
    }

    /**
     * 一覧表示
     */
    public function index(): View
    {
        return view("admin.web.purpose.index", [
            'purposes' => $this->purposeService->paginate(30),
        ]);
    }

    /**
     * 登録画面
     */
    public function create(): View
    {
        return view("admin.web.purpose.create");
    }

    /**
     * 登録処理
     */
    public function store(PurposeStoreRequest $request)
    {
        if (($purpose = $this->purposeService->create($request->all()))) {
            return redirect()->route('admin.web.purposes.index', ['sort'=>'id','direction'=>'desc'])->with('success_message', "ID: {$purpose->id}「{$purpose->name}」を登録しました");
        }
    }

    /**
     * 更新ページ
     */
    public function edit($id): View
    {
        if (($purpose = $this->purposeService->find((int)$id))) {
            return view('admin.web.purpose.edit', compact('purpose'));
        }
        abort(404);
    }

    /**
     * 更新処理
     */
    public function update(PurposeUpdateRequest $request, $id)
    {
        if ($this->purposeService->update((int)$id, $request->all()) > 0) {
            return redirect()->route('admin.web.purposes.edit', $id)->with('success_message', "目的ID: {$id} を更新しました");
        }
        abort(409);
    }

    /**
     * 削除処理
     */
    public function destroy(PurposeDestroyRequest $request, $id)
    {
        if ($this->purposeService->delete((int)$id)>0) {
            return redirect()->route('admin.web.purposes.index', ['sort'=>'id','direction'=>'desc'])->with('success_message', "目的ID: {$id} を削除しました");
        }
        abort(404); // not found
    }
}
