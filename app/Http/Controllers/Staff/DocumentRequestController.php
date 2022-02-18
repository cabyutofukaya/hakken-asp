<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\DocumentRequestStoreRequest;
use App\Http\Requests\Staff\DocumentRequestUpdateRequest;
use App\Models\DocumentRequest;
use App\Services\DocumentRequestService;
use Gate;
use Hashids;
use Illuminate\Http\Request;

class DocumentRequestController extends AppController
{
    public function __construct(DocumentRequestService $documentRequestService)
    {
        $this->documentRequestService = $documentRequestService;
    }

    /**
     * 共通設定作成form
     *
     * @return \Illuminate\Http\Response
     */
    public function create($agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('create', [new DocumentRequest]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.document_category.request.create');
    }

    /**
     * 共通設定作成処理
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DocumentRequestStoreRequest $request, $agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('create', [new DocumentRequest]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->validated();
        $input['agency_id'] = auth('staff')->user()->agency_id;
        
        if ($documentRequest = $this->documentRequestService->create($input)) {
            return redirect()->route('staff.system.document.index', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.document_categories.DOCUMENT_CATEGORY_REQUEST')])->with('success_message', "「{$documentRequest->name}」を登録しました");
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
        $documentRequest = $this->documentRequestService->find((int)$decodeId);

        // 認可チェック
        $response = Gate::inspect('view', [$documentRequest]);
        if (!$response->allowed()) {
            abort(403);
        }
        return view('staff.document_category.request.edit', compact('documentRequest'));
    }

    /**
     * 共通設定更新処理
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(DocumentRequestUpdateRequest $request, $agencyAccount, $id)
    {
        $decodeId = Hashids::decode($id)[0] ?? null;
        $old = $this->documentRequestService->find((int)$decodeId);

        // 認可チェック
        $response = Gate::inspect('update', [$old]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->validated();

        if ($new = $this->documentRequestService->update($decodeId, $input)) {
            return redirect()->route('staff.system.document.index', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.document_categories.DOCUMENT_CATEGORY_REQUEST')])->with('success_message', "「{$new->name}」を更新しました");
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
        $documentRequest = $this->documentRequestService->find((int)$decodeId);

        // 認可チェック
        $response = Gate::inspect('delete', [$documentRequest]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }
        
        if ($this->documentRequestService->delete((int)$decodeId, true) > 0) {
            return redirect()->route('staff.system.document.index', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.document_categories.DOCUMENT_CATEGORY_REQUEST')])->with('decline_message', "「{$documentRequest->name}」を削除しました");
        }
        abort(400); // Bad Request
    }

}
