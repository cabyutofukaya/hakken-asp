<?php
declare(strict_types=1);

namespace App\Http\Controllers\Admin\Web;

use App\Models\BaseWebUser;
use App\Http\Controllers\Controller;
use App\Services\BaseWebUserService;
use App\Http\Requests\Admin\WebUserUpdateRequest;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Gate;

class BaseWebUserController extends Controller
{
    public function __construct(BaseWebUserService $baseWebUserService)
    {
        $this->baseWebUserService = $baseWebUserService;
    }

    /**
     * 一覧表示
     */
    public function index(): View
    {
        $response = Gate::inspect('viewAny', [new BaseWebUser]);
        if (!$response->allowed()) {
            abort(403);
        }// 認可チェック

        return view("admin.web.web_user.index", [
            'webUsers' => $this->baseWebUserService->paginate([], 30),
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
        $webUser = $this->baseWebUserService->find($id);

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
        $webUser = $this->baseWebUserService->find($id);
        $response = Gate::inspect('update', [$webUser]);
        if (!$response->allowed()) {
            return back()->withInput()->withErrors(array('auth_error' => $response->message()));
        }// 認可チェック

        if ($webUser = $this->baseWebUserService->update($id, $request->all())) {
            return redirect()->route('admin.web.web_users.edit', $id)->with('success_message', "顧客: {$webUser->web_user_number} を更新しました");
        }
        abort(409);
    }
}
