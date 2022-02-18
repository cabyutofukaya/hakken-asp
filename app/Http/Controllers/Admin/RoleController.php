<?php

namespace App\Http\Controllers\Admin;

use Gate;
use App\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\RoleService;
use App\Http\Requests\Admin\RoleStoreRequest;
use App\Http\Requests\Admin\RoleUpdateRequest;
use App\Http\Requests\Admin\RoleDestroyRequest;


class RoleController extends Controller
{
    public function __construct(RoleService $roleService)
    {
        $this->roleService = $roleService;
    }

    public function index()
    {
        return view('admin.role.index', [
            'roles' => $this->roleService->all()
        ]);
    }

    public function create()
    {
        return view('admin.role.create', [
            'roleItems' => $this->roleService->getRoleItems()
        ]);
    }

    public function store(RoleStoreRequest $request)
    {
        // 認可チェック
        $response = Gate::inspect('create', [new Role]);
        if (!$response->allowed()) {
            return back()->withInput()->withErrors(array('auth_error' => $response->message()));
        }

        if (($role = $this->roleService->create($request->all()))) {
            return redirect()->route('admin.roles.index')->with('success_message', "権限ID: {$role->id}「{$role->name}」を登録しました");
        }
    }

    public function edit($id)
    {
        return view("admin.role.edit", [
            'role' => $this->roleService->find((int)$id),
            'roleItems' => $this->roleService->getRoleItems()
        ]);
    }

    public function update(RoleUpdateRequest $request, $id)
    {
        $role = $this->roleService->find((int)$id);
        // 認可チェック
        $response = Gate::inspect('update', [$role]);
        if (!$response->allowed()) {
            return back()->withInput()->withErrors(array('auth_error' => $response->message()));
        }

        if ($this->roleService->update((int)$id, $request->all())) {
            return redirect()->route('admin.roles.index')->with('success_message', "権限ID: {$id} を更新しました");
        }
    }

    /**
     * 削除処理
     *
     * @param int $id 権限ID
     */
    public function destroy(RoleDestroyRequest $request, $id)
    {
        $role = $this->roleService->find((int)$id);
        // 認可チェック
        $response = Gate::inspect('delete', [$role]);
        if (!$response->allowed()) {
            return back()->withInput()->withErrors(array('auth_error' => $response->message()));
        }
        
        if ($this->roleService->delete((int)$id) > 0) {
            return redirect()->route('admin.roles.index')->with('success_message', "権限ID: {$id} を削除しました");
        }
        abort(404); // not found
    }
}
