<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\DocumentRequestAllStoreRequest;
use App\Http\Requests\Staff\DocumentRequestAllUpdateRequest;
use App\Models\DocumentRequestAll;
use App\Services\DocumentRequestAllService;
use Gate;
use Hashids;
use Illuminate\Http\Request;

class DocumentRequestAllController extends AppController
{
    public function __construct(DocumentRequestAllService $documentRequestAllService)
    {
        $this->documentRequestAllService = $documentRequestAllService;
    }

    /**
     * 共通設定作成form
     *
     * @return \Illuminate\Http\Response
     */
    public function create($agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('create', [new DocumentRequestAll]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.document_category.request_all.create');
    }

    /**
     * 共通設定作成処理
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DocumentRequestAllStoreRequest $request, $agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('create', [new DocumentRequestAll]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->validated();
        $input['agency_id'] = auth('staff')->user()->agency_id;
        
        if ($documentRequestAll = $this->documentRequestAllService->create($input)) {
            return redirect()->route('staff.system.document.index', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.document_categories.DOCUMENT_CATEGORY_REQUEST_ALL')])->with('success_message', "「{$documentRequestAll->name}」を登録しました");
        }
        abort(500);
    }

    /**
     * 共通設定編集form
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($agencyAccount, $id)
    {
        $decodeId = Hashids::decode($id)[0] ?? null;
        $documentRequestAll = $this->documentRequestAllService->find((int)$decodeId);

        // 認可チェック
        $response = Gate::inspect('update', [$documentRequestAll]);
        if (!$response->allowed()) {
            abort(403);
        }
        return view('staff.document_category.request_all.edit', compact('documentRequestAll'));
    }

    /**
     * 共通設定更新処理
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(DocumentRequestAllUpdateRequest $request, $agencyAccount, $id)
    {
        $decodeId = Hashids::decode($id)[0] ?? null;
        $old = $this->documentRequestAllService->find((int)$decodeId);

        // 認可チェック
        $response = Gate::inspect('update', [$old]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->validated();

        if ($new = $this->documentRequestAllService->update($decodeId, $input)) {
            return redirect()->route('staff.system.document.index', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.document_categories.DOCUMENT_CATEGORY_REQUEST_ALL')])->with('success_message', "「{$new->name}」を更新しました");
        }
        abort(500);
    }

    /**
     * 共通設定削除
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($agencyAccount, $id)
    {
        $decodeId = Hashids::decode($id)[0] ?? null;
        $documentRequestAll = $this->documentRequestAllService->find((int)$decodeId);

        // 認可チェック
        $response = Gate::inspect('delete', [$documentRequestAll]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }
        
        if ($this->documentRequestAllService->delete((int)$decodeId, true) > 0) {
            return redirect()->route('staff.system.document.index', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.document_categories.DOCUMENT_CATEGORY_REQUEST_ALL')])->with('decline_message', "「{$documentRequestAll->name}」を削除しました");
        }
        abort(400); // Bad Request
    }

}
