<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\DocumentReceiptStoreRequest;
use App\Http\Requests\Staff\DocumentReceiptUpdateRequest;
use App\Models\DocumentReceipt;
use App\Services\DocumentReceiptService;
use Gate;
use Hashids;
use Illuminate\Http\Request;

class DocumentReceiptController extends AppController
{
    public function __construct(DocumentReceiptService $documentReceiptService)
    {
        $this->documentReceiptService = $documentReceiptService;
    }

    /**
     * 領収書作成form
     *
     * @return \Illuminate\Http\Response
     */
    public function create($agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('create', [new DocumentReceipt]);
        if (!$response->allowed()) {
            abort(403);
        }

        return view('staff.document_category.receipt.create');
    }

    /**
     * 領収書作成処理
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DocumentReceiptStoreRequest $request, $agencyAccount)
    {
        // 認可チェック
        $response = Gate::inspect('create', [new DocumentReceipt]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->validated();
        $input['agency_id'] = auth('staff')->user()->agency_id;
        
        if ($documentReceipt = $this->documentReceiptService->create($input)) {
            return redirect()->route('staff.system.document.index', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.document_categories.DOCUMENT_CATEGORY_RECEIPT')])->with('success_message', "「{$documentReceipt->name}」を登録しました");
        }
        abort(500);
    }

    /**
     * 領収書編集form
     *
     * @return \Illuminate\Http\Response
     */
    public function edit($agencyAccount, $id)
    {
        $decodeId = Hashids::decode($id)[0] ?? null;
        $documentReceipt = $this->documentReceiptService->find((int)$decodeId);

        // 認可チェック
        $response = Gate::inspect('view', [$documentReceipt]);
        if (!$response->allowed()) {
            abort(403);
        }
        return view('staff.document_category.receipt.edit', compact('documentReceipt'));
    }

    /**
     * 領収書更新処理
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(DocumentReceiptUpdateRequest $request, $agencyAccount, $id)
    {
        $decodeId = Hashids::decode($id)[0] ?? null;
        $old = $this->documentReceiptService->find((int)$decodeId);

        // 認可チェック
        $response = Gate::inspect('update', [$old]);
        if (!$response->allowed()) {
            return $this->forbiddenRedirect($response->message());
        }

        $input = $request->validated();

        if ($new = $this->documentReceiptService->update($decodeId, $input)) {
            return redirect()->route('staff.system.document.index', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.document_categories.DOCUMENT_CATEGORY_RECEIPT')])->with('success_message', "「{$new->name}」を更新しました");
        }
        abort(500);
    }

    // /**
    //  * 領収書削除
    //  *
    //  * @param  int  $id
    //  * @return \Illuminate\Http\Response
    //  */
    // public function destroy($agencyAccount, $id)
    // {
    //     $decodeId = Hashids::decode($id)[0] ?? null;
    //     $documentReceipt = $this->documentReceiptService->find((int)$decodeId);

    //     // 認可チェック
    //     $response = Gate::inspect('delete', [$documentReceipt]);
    //     if (!$response->allowed()) {
    //         return $this->forbiddenRedirect($response->message());
    //     }
        
    //     if ($this->documentReceiptService->delete((int)$decodeId, true) > 0) {
    //         return redirect()->route('staff.system.document.index', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.document_categories.DOCUMENT_CATEGORY_RECEIPT')])->with('decline_message', "「{$documentReceipt->name}」を削除しました");
    //     }
    //     abort(400); // Bad Request
    // }

}
