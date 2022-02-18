<?php
declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StaffDestroyRequest;
use App\Http\Requests\Admin\StaffShowRequest;
use App\Http\Requests\Admin\StaffStoreRequest;
use App\Http\Requests\Admin\StaffUpdateRequest;
use App\Models\Staff;
use App\Services\AgencyService;
use App\Services\PrefectureService;
use App\Services\StaffService;
use App\Services\RoleService;
use Gate;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function __construct(StaffService $staffService, RoleService $roleService)
    {
        $this->staffService = $staffService;
        $this->roleService = $roleService;
    }

    /**
     * 詳細表示
     */
    public function show($agencyId, $staffId): View
    {
        // 認可チェック
        $staff = $this->staffService->find((int)$staffId);
        Gate::authorize('view', $staff);

        return view("admin.staff.show", compact('staff'));
    }

    /**
     * 登録画面
     */
    public function create($agencyId): View
    {
        return view("admin.staff.create", [
            'agencyId' => $agencyId,
            'statuses' => $this->staffService->getStatuses(),
            'roles' => $this->roleService->all()
            ]);
    }

    /**
     * 登録処理
     *
     * @param int $agencyId 会社ID
     */
    public function store(StaffStoreRequest $request, $agencyId)
    {
        // 認可チェック
        $response = Gate::inspect('create', [new Staff, (int)$agencyId]);
        if (!$response->allowed()) {
            return back()->withInput()->withErrors(array('auth_error' => $response->message()));
        }

        if (($staff = $this->staffService->create((int)$agencyId, $request->all()))) {
            return redirect()->route('admin.agencies.show', $agencyId)->with('success_message', "スタッフID: {$staff->id}「{$staff->name}」を登録しました");
        }
    }

    /**
     * 更新ページ
     */
    public function edit($agencyId, $staffId): View
    {
        if (($staff = $this->staffService->find((int)$staffId))) {
            $statuses = $this->staffService->getStatuses(); // 定数データ
            $roles = $this->roleService->all();
            
            return view('admin.staff.edit', compact('agencyId', 'staff', 'statuses', 'roles'));
        }
        abort(404);
    }

    /**
     * 更新処理
     */
    public function update(StaffUpdateRequest $request, $agencyId, $staffId)
    {
        // 認可チェック
        $staff = $this->staffService->find((int)$staffId);
        Gate::authorize('update', $staff);
        
        try {
            if ($this->staffService->update((int)$agencyId, (int)$staffId, $request->all())) {
                return redirect()->route('admin.staffs.edit', [$agencyId, $staffId])->with('success_message', "スタッフID: {$staffId} を更新しました");
            }
        } catch(ExclusiveLockException $e) {
            return redirect()->back()->with('auth_error', '排他エラーです。');
        }
        abort(409);
    }

    /**
     * 削除処理
     *
     * @param int $agencyId 会社ID
     * @param int $staffId スタッフID
     */
    public function destroy(StaffDestroyRequest $request, $agencyId, $staffId)
    {
        // 認可チェック
        $staff = $this->staffService->find((int)$staffId);
        Gate::authorize('delete', $staff);
        
        if ($this->staffService->delete((int)$agencyId, (int)$staffId) > 0) {
            return redirect()->route('admin.agencies.show', $agencyId)->with('success_message', "スタッフID: {$staffId} を削除しました");
        }
        abort(404); // not found
    }
}
