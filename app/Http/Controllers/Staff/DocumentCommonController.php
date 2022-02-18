<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\DocumentCommonStoretRequest;
use App\Http\Requests\Staff\DocumentCommonUpdateRequest;
use App\Models\DocumentCommon;
use App\Services\DocumentCommonService;
use Gate;
use Hashids;
use Illuminate\Http\Request;

class DocumentCommonController extends AppController
{
    public function __construct(DocumentCommonService $documentCommonService)
    {
        $this->documentCommonService = $documentCommonService;
    }

    /**
     * 共通設定作成form
     *
     * @return \Illuminate\Http\Response
     */
    public function create($agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('create', [new DocumentCommon]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.document_category.document_common.create');
    }

    /**
     * 共通設定作成処理
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DocumentCommonStoretRequest $request, $agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('create', [new DocumentCommon]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->validated();
        $input['agency_id'] = auth('staff')->user()->agency_id;

        if ($documentCommon = $this->documentCommonService->create($input)) {
            return redirect()->route('staff.system.document.index', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.document_categories.DOCUMENT_CATEGORY_COMMON')])->with('success_message', "「{$documentCommon->name}」を登録しました");
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
        $documentCommon = $this->documentCommonService->find((int)$decodeId);

        // 認可チェック
        $response = Gate::inspect('view', [$documentCommon]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.document_category.document_common.edit', compact('documentCommon'));
    }

    /**
     * 共通設定更新処理
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(DocumentCommonUpdateRequest $request, $agencyAccount, $id)
    {
        $decodeId = Hashids::decode($id)[0] ?? null;
        $old = $this->documentCommonService->find((int)$decodeId);

        // 認可チェック
        $response = Gate::inspect('update', [$old]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->validated();

        if ($new = $this->documentCommonService->update($decodeId, $input)) {
            return redirect()->route('staff.system.document.index', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.document_categories.DOCUMENT_CATEGORY_COMMON')])->with('success_message', "「{$new->name}」を更新しました");
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
        $documentCommon = $this->documentCommonService->find((int)$decodeId);

        // 認可チェック
        $response = Gate::inspect('delete', [$documentCommon]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }
        
        if ($this->documentCommonService->delete((int)$decodeId, true) > 0) {
            return redirect()->route('staff.system.document.index', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.document_categories.DOCUMENT_CATEGORY_COMMON')])->with('decline_message', "「{$documentCommon->name}」を削除しました");
        }
        abort(400); // Bad Request
    }
}
