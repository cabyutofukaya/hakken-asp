<?php
declare(strict_types=1);

namespace App\Http\Controllers\Admin\Web;

use App\Models\SystemNews;
use App\Http\Controllers\Controller;
use App\Services\SystemNewsService;
use App\Http\Requests\Admin\SystemNewsStoreRequest;
use App\Http\Requests\Admin\SystemNewsUpdateRequest;
use App\Http\Requests\Admin\SystemNewsDeleteRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Gate;

class SystemNewsController extends Controller
{
    public function __construct(SystemNewsService $systemNewsService)
    {
        $this->systemNewsService = $systemNewsService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() : View
    {
        // 認可チェック
        Gate::authorize('view', new SystemNews);

        $systemNews = $this->systemNewsService->paginate([], 10);

        return view("admin.system_news.index", compact('systemNews'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // 認可チェック
        Gate::authorize('create', new SystemNews);

        return view("admin.system_news.create");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SystemNewsStoreRequest $request)
    {
        // 認可チェック
        Gate::authorize('create', new SystemNews);

        try {
            $input = $request->validated();
            $systemNews = \DB::transaction(function () use ($input) {
                return $this->systemNewsService->create($input);
            });
            if ($systemNews) {
                return redirect()->route('admin.web.system_news.index')->with('success_message', "通知ID: {$systemNews->id} を登録しました");
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', '登録処理に失敗しました。');
        }

        abort(500);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(int $id)
    {
        $systemNews = $this->systemNewsService->find($id);

        // 認可チェック
        Gate::authorize('view', $systemNews);

        return view("admin.system_news.edit", compact('systemNews'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(SystemNewsUpdateRequest $request, int $id)
    {
        $systemNews = $this->systemNewsService->find($id);

        // 認可チェック
        Gate::authorize('update', $systemNews);

        try {
            $input = $request->validated();
            $result = \DB::transaction(function () use ($id, $input) {
                return $this->systemNewsService->update($id, $input);
            });
            if ($result) {
                return redirect()->route('admin.web.system_news.index')->with('success_message', "通知ID: {$id} を更新しました");
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', '更新処理に失敗しました。');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function delete(SystemNewsDeleteRequest $request)
    {
        $systemNews = $this->systemNewsService->find((int)$request->input('id'));

        // 認可チェック
        Gate::authorize('delete', $systemNews);

        try {
            $input = $request->validated();
            $result = \DB::transaction(function () use ($systemNews) {
                return $this->systemNewsService->delete($systemNews->id, true); // 論理削除
            });
            if ($result) {
                return redirect()->route('admin.web.system_news.index')->with('success_message', "通知ID: {$systemNews->id} を削除しました");
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', '削除処理に失敗しました。');
        }
    }
}
