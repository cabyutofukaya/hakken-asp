<?php
declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserDestroyRequest;
use App\Http\Requests\Admin\UserStoreRequest;
use App\Http\Requests\Admin\UserUpdateRequest;
use App\Models\User;
use App\Services\AgencyService;
use App\Services\InflowService;
use App\Services\UserSequenceService;
use App\Services\UserService;
use DB;
use Gate;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    const LIST_PER_PAGE = 15; // 1ページ表示件数

    private $userService;
    private $agencyService;
    private $inflowService;

    public function __construct(UserService $userService, AgencyService $agencyService, InflowService $inflowService, UserSequenceService $userSequenceService)
    {
        $this->userService = $userService;
        $this->agencyService = $agencyService;
        $this->inflowService = $inflowService;
        $this->userSequenceService = $userSequenceService;
    }

    /**
     * 一覧表示
     */
    public function index(): View
    {
        // 認可チェック
        Gate::authorize('viewAny', new User);

        return view("admin.user.index", [
            'users' => $this->userService->paginate(self::LIST_PER_PAGE, ['agency', 'inflow']),
            'status' => $this->userService->getStatusSelect(), // 定数データ
        ]);
    }

    /**
     * 詳細表示
     */
    public function show($id): View
    {
        $user = $this->userService->find((int)$id);
        // 認可チェック
        Gate::authorize('view', $user);

        return view("admin.user.show", compact('user'));
    }

    /**
     * 登録画面
     */
    public function create(): View
    {
        return view("admin.user.create", [
            'status' => $this->userService->getStatusSelect(),
            'inflows' => [''=>'なし'] + $this->inflowService->getNameList(),
            'agency' => old('agency_id') ? $this->agencyService->find((int)old('agency_id')) : null
        ]);
    }

    /**
     * 登録処理
     *
     * 会社毎に登録されたカスタム項目（user_custom_items）は編集不可
     */
    public function store(UserStoreRequest $request)
    {
        try {
            $user = DB::transaction(function () use ($request) {
                $input = $request->all();
        
                return $this->userService->createAspUser($input, true);
            });
            if ($user) {
                return redirect()->route('admin.users.index', ['sort'=>'id','direction'=>'desc'])->with('success_message', "ID: {$user->id}「{$user->name}」さんを登録しました");
            }    
        }catch(\Exception $e){
            // 
        }
        abort(500);
    }

    /**
     * 更新ページ
     *
     * 会社毎に登録されたカスタム項目（user_custom_items）も編集可
     */
    public function edit($id): View
    {
        if (($user = $this->userService->find((int)$id))) {
            $inflows = [''=>'なし'] + $this->inflowService->getNameList();
            $status = $this->userService->getStatusSelect(); // 定数データ
            return view('admin.user.edit', compact('user', 'inflows', 'status'));
        }
        abort(404);
    }

    /**
     * 更新処理
     */
    public function update(UserUpdateRequest $request, $id)
    {
        // 認可チェック
        $user = $this->userService->find((int)$id);
        Gate::authorize('update', $user);
        
        try {
            $user = DB::transaction(function () use ($id, $request) {
                return $this->userService->update((int)$id, $request->all());
            });
    
            if ($user) {
                return redirect()->route('admin.users.edit', $id)->with('success_message', "ユーザーID: {$id} を更新しました");
            }
        } catch (ExclusiveLockException $e) {
            return redirect()->back()->with('auth_error', '排他エラーです。');
        }

        abort(409);
    }

    // 削除
    public function destroy(UserDestroyRequest $request, $id)
    {
        // 認可チェック
        $user = $this->userService->find((int)$id);
        Gate::authorize('delete', $user);

        if ($this->userService->delete((int)$id)>0) {
            return redirect()->route('admin.users.index', ['sort'=>'id','direction'=>'desc'])->with('success_message', "ユーザーID: {$id} を削除しました");
        }
        abort(404); // not found
    }
}
