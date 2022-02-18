<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\SupplierStoretRequest;
use App\Http\Requests\Staff\SupplierUpdateRequest;
use App\Models\Supplier;
use App\Services\SupplierService;
use Gate;
use DB;
use Hashids;
use Exception;
use Log;
use Illuminate\Http\Request;

class SupplierController extends AppController
{
    public function __construct(SupplierService $supplierService)
    {
        $this->supplierService = $supplierService;
    }

    /**
     * 一覧
     *
     * @return \Illuminate\Http\Response
     */
    public function index($agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('viewAny', [new Supplier]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.supplier.index');
    }

    /**
     * 作成form
     *
     * @return \Illuminate\Http\Response
     */
    public function create($agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('create', [new Supplier]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.supplier.create');
    }

    /**
     * 作成処理
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(SupplierStoretRequest $request, $agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('create', [new Supplier]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $agencyId = auth('staff')->user()->agency_id;

        $input = $request->all(); // カスタムフィールドがあるので、validatedではなくallで取得
        // 保存用配列に会社IDをセット
        $input['agency_id'] = $agencyId;
        foreach ($input['supplier_account_payables'] as $k => $v) {
            $input['supplier_account_payables'][$k]['agency_id'] = $agencyId;
        }

        try {
            $supplier = DB::transaction(function () use ($input) {
                return $this->supplierService->create($input);
            });

            if ($supplier) {
                return redirect()->route('staff.master.supplier.index', [$agencyAccount])->with('success_message', "「{$supplier->name}」を登録しました");
            }
        } catch (Exception $e) {
            Log::error($e);
        }
        abort(500);
    }

    /**
     * 更新form
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($agencyAccount, $id)
    {
        $decodeId = Hashids::decode($id)[0] ?? null;
        $supplier = $this->supplierService->find((int)$decodeId);

        // 認可チェック
        $response = Gate::inspect('view', [$supplier]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.supplier.edit', compact('supplier'));
    }

    /**
     * 更新処理
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(SupplierUpdateRequest $request, $agencyAccount, $id)
    {
        $decodeId = Hashids::decode($id)[0] ?? null;
        $old = $this->supplierService->find((int)$decodeId);

        // 認可チェック
        $response = Gate::inspect('update', [$old]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $agencyId = auth('staff')->user()->agency_id;

        $input = $request->all(); // カスタムフィールドがあるので、validatedではなくallで取得
        // 保存用配列に会社IDをセット
        $input['agency_id'] = $agencyId;
        foreach ($input['supplier_account_payables'] as $k => $v) {
            $input['supplier_account_payables'][$k]['agency_id'] = $agencyId;
        }

        try {
            $new = DB::transaction(function () use ($decodeId, $input) {
                return $this->supplierService->update($decodeId, $input);
            });
            
            if ($new) {
                return redirect()->route('staff.master.supplier.index', [$agencyAccount])->with('success_message', "「{$new->name}」を更新しました");
            }
        } catch (Exception $e) {
            Log::error($e);
        }

        abort(500);
    }

    /**
     * 削除
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($agencyAccount, $id)
    {
        $decodeId = Hashids::decode($id)[0] ?? null;
        $supplier = $this->supplierService->find((int)$decodeId);

        // 認可チェック
        $response = Gate::inspect('delete', [$supplier]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }
        
        if ($this->supplierService->delete((int)$decodeId, true) > 0) {
            return redirect()->route('staff.master.supplier.index', [$agencyAccount])->with('decline_message', "「{$supplier->name}」を削除しました");
        }
        abort(400); // Bad Request
    }
}
