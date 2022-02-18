<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\DocumentQuoteStoreRequest;
use App\Http\Requests\Staff\DocumentQuoteUpdateRequest;
use App\Models\DocumentQuote;
use App\Services\DocumentQuoteService;
use Gate;
use Hashids;
use Illuminate\Http\Request;

class DocumentQuoteController extends AppController
{
    public function __construct(DocumentQuoteService $documentQuoteService)
    {
        $this->documentQuoteService = $documentQuoteService;
    }

    /**
     * 共通設定作成form
     *
     * @return \Illuminate\Http\Response
     */
    public function create($agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('create', [new DocumentQuote]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.document_category.quote.create');
    }

    /**
     * 共通設定作成処理
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DocumentQuoteStoreRequest $request, $agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('create', [new DocumentQuote]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->validated();
        $input['agency_id'] = auth('staff')->user()->agency_id;

        if ($documentQuote = $this->documentQuoteService->create($input)) {
            return redirect()->route('staff.system.document.index', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.document_categories.DOCUMENT_CATEGORY_QUOTE')])->with('success_message', "「{$documentQuote->name}」を登録しました");
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
        $documentQuote = $this->documentQuoteService->find((int)$decodeId);

        // 認可チェック
        $response = Gate::inspect('view', [$documentQuote]);
        if (!$response->allowed()) {
            abort(403);
        }
        return view('staff.document_category.quote.edit', compact('documentQuote'));
    }

    /**
     * 共通設定更新処理
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(DocumentQuoteUpdateRequest $request, $agencyAccount, $id)
    {
        $decodeId = Hashids::decode($id)[0] ?? null;
        $old = $this->documentQuoteService->find((int)$decodeId);

        // 認可チェック
        $response = Gate::inspect('update', [$old]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->validated();

        if ($new = $this->documentQuoteService->update($decodeId, $input)) {
            return redirect()->route('staff.system.document.index', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.document_categories.DOCUMENT_CATEGORY_QUOTE')])->with('success_message', "「{$new->name}」を更新しました");
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
        $documentQuote = $this->documentQuoteService->find((int)$decodeId);

        // 認可チェック
        $response = Gate::inspect('delete', [$documentQuote]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }
        
        if ($this->documentQuoteService->delete((int)$decodeId, true) > 0) {
            return redirect()->route('staff.system.document.index', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.document_categories.DOCUMENT_CATEGORY_QUOTE')])->with('decline_message', "「{$documentQuote->name}」を削除しました");
        }
        abort(400); // Bad Request
    }

}
