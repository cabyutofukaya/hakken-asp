<?php
declare(strict_types=1);

namespace App\Http\Controllers\Admin\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InterestStoreRequest;
use App\Http\Requests\Admin\InterestUpdateRequest;
use App\Http\Requests\Admin\InterestDestroyRequest;
use App\Services\InterestService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InterestController extends Controller
{
    private $interestService;

    public function __construct(InterestService $interestService)
    {
        $this->interestService = $interestService;
    }

    /**
     * 一覧表示
     */
    public function index(): View
    {
        return view("admin.web.interest.index", [
            'interests' => $this->interestService->paginate(30),
        ]);
    }

    /**
     * 登録画面
     */
    public function create(): View
    {
        return view("admin.web.interest.create");
    }

    /**
     * 登録処理
     */
    public function store(InterestStoreRequest $request)
    {
        if (($interest = $this->interestService->create($request->all()))) {
            return redirect()->route('admin.web.interests.index', ['sort'=>'id','direction'=>'desc'])->with('success_message', "ID: {$interest->id}「{$interest->name}」を登録しました");
        }
    }

    /**
     * 更新ページ
     */
    public function edit($id): View
    {
        if (($interest = $this->interestService->find((int)$id))) {
            return view('admin.web.interest.edit', compact('interest'));
        }
        abort(404);
    }

    /**
     * 更新処理
     */
    public function update(InterestUpdateRequest $request, $id)
    {
        if ($this->interestService->update((int)$id, $request->all()) > 0) {
            return redirect()->route('admin.web.interests.edit', $id)->with('success_message', "目的ID: {$id} を更新しました");
        }
        abort(409);
    }

    /**
     * 削除処理
     */
    public function destroy(InterestDestroyRequest $request, $id)
    {
        if ($this->interestService->delete((int)$id)>0) {
            return redirect()->route('admin.web.interests.index', ['sort'=>'id','direction'=>'desc'])->with('success_message', "目的ID: {$id} を削除しました");
        }
        abort(404); // not found
    }
}
