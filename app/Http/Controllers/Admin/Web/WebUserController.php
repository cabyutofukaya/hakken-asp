<?php
declare(strict_types=1);

namespace App\Http\Controllers\Admin\Web;

use App\Models\WebUser;
use App\Http\Controllers\Controller;
use App\Services\WebUserService;
use App\Http\Requests\Admin\WebUserUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Gate;

class WebUserController extends Controller
{
    public function __construct(WebUserService $webUserService)
    {
        $this->webUserService = $webUserService;
    }

    /**
     * 一覧表示
     */
    public function index(): View
    {
        $response = Gate::inspect('viewAny', [new WebUser]);
        if (!$response->allowed()) {
            abort(403);
        }// 認可チェック

        return view("admin.web.web_user.index", [
            'webUsers' => $this->webUserService->paginate([], 30),
        ]);
    }

    public function show($id){
        //
    }

    /**
     * 編集
     */
    public function edit(int $id)
    {
        $webUser = $this->webUserService->find($id);

        $response = Gate::inspect('update', [$webUser]);
        if (!$response->allowed()) {
            abort(403);
        }// 認可チェック

        return view("admin.web.web_user.edit", compact('webUser'));
    }

    /**
     * 更新処理
     */
    public function update(WebUserUpdateRequest $request, int $id)
    {
        $webUser = $this->webUserService->find($id);
        $response = Gate::inspect('update', [$webUser]);
        if (!$response->allowed()) {
            return back()->withInput()->withErrors(array('auth_error' => $response->message()));
        }// 認可チェック

        if ($webUser = $this->webUserService->update($id, $request->all())) {
            return redirect()->route('admin.web.web_users.edit', $id)->with('success_message', "顧客: {$webUser->web_user_number} を更新しました");
        }
        abort(409);
    }
}
