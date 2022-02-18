<?php
declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Exceptions\ExclusiveLockException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AgencyDestroyRequest;
use App\Http\Requests\Admin\AgencyStoreRequest;
use App\Http\Requests\Admin\AgencyUpdateRequest;
use App\Models\Agency;
use App\Services\AgencyService;
use App\Services\RoleService;
use App\Services\StaffService;
use DB;
use Gate;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgencyController extends Controller
{
    const AGENCY_LIST_PER_PAGE = 15; // 1ページ表示件数（会社リスト）
    const STAFF_LIST_PER_PAGE = 20; // 1ページ表示件数（スタッフリスト）

    public function __construct(
        AgencyService $agencyService,
        RoleService $roleService,
        StaffService $staffService
    ) {
        $this->agencyService = $agencyService;
        $this->roleService = $roleService;
        $this->staffService = $staffService;
    }

    /**
     * 一覧表示
     */
    public function index()
    {
        $searchParam = [];// 検索パラメータ
        foreach (['company_name', 'tel', 'address', 'business_scope', 'registration_type', 'travel_agency_association', 'fair_trade_council', 'iata', 'etbt', 'bond_guarantee'] as $p) {
            $searchParam[$p] = \Request::get($p);
        }

        $agencies = $this->agencyService->paginate($searchParam, self::AGENCY_LIST_PER_PAGE, ['prefecture', 'staffs']);

        return view("admin.agency.index", compact('agencies', 'searchParam'));
    }

    /**
     * 詳細表示
     */
    public function show($id)
    {
        return view("admin.agency.show", [
            'agency' => $this->agencyService->find((int)$id),
            'staffs' => $this->staffService->paginateByAgencyId((int)$id, [], self::STAFF_LIST_PER_PAGE),
            'statuses' => $this->staffService->getStatuses(),
            'roles' => $this->roleService->all()
            ]);
    }

    /**
     * 登録画面
     */
    public function create()
    {
        return view("admin.agency.create");
    }

    /**
     * 登録処理
     */
    public function store(AgencyStoreRequest $request)
    {
        $response = Gate::inspect('create', [new Agency]);
        if (!$response->allowed()) {
            return back()->withInput()->withErrors(array('auth_error' => $response->message()));
        }// 認可チェック

        $input = $request->validated();

        try {
            // ファイルアップロード
            foreach (['agreement_file','terms_file'] as $f) {
                $file = $request->file("upload_" . $f);
                if (!is_null($file)) {
                    $uploadPath = \Storage::disk('s3')->putFile(config('consts.const.UPLOAD_PDF_DIR'), $file, 'public');
                    $fileName = basename($uploadPath);
                    $input[$f] = $fileName;
                }
            }

            $agency = DB::transaction(function () use ($input) {
                return $this->agencyService->create($input);
            });

            if ($agency) {
                return redirect()->route('admin.agencies.index', ['sort'=>'id','direction'=>'desc'])->with('success_message', "ID: {$agency->id}「{$agency->company_name}」を登録しました");
            }
        } catch (\Exception $e) {
            // 識別番号発行エラー、ファイルアップロードエラー等
            \Log::error($e);
        }
        abort(404);
    }

    /**
     * 更新ページ
     */
    public function edit($id)
    {
        if (($agency = $this->agencyService->find((int)$id))) {
            return view('admin.agency.edit', compact('agency'));
        }
        abort(404);
    }

    /**
     * 更新処理
     */
    public function update(AgencyUpdateRequest $request, int $id)
    {
        $agency = $this->agencyService->find($id);

        // 認可チェック
        $response = Gate::inspect('update', [$agency, $request->number_staff_allowed]);
        if (!$response->allowed()) {
            return back()->withInput()->withErrors(array('auth_error' => $response->message()));
        }

        $input = $request->validated();
        
        try {
            // ファイルアップロード
            foreach (['agreement_file','terms_file'] as $f) {
                $file = $request->file("upload_" . $f);
                if (!is_null($file)) {
                    $uploadPath = \Storage::disk('s3')->putFile(config('consts.const.UPLOAD_PDF_DIR'), $file, 'public');
                    $fileName = basename($uploadPath);
                    $input[$f] = $fileName;
                }
            }

            if ($this->agencyService->update($id, $input)) {
                return redirect()->route('admin.agencies.edit', $id)->with('success_message', "会社ID: {$id} を更新しました");
            }
        } catch (ExclusiveLockException $e) {
            return redirect()->back()->with('auth_error', '排他エラーです。');
        }

        abort(409);
    }

    // /**
    //  * 削除処理
    //  */
    // public function destroy($id)
    // {
    // }
}
