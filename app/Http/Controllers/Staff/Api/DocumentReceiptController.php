<?php

namespace App\Http\Controllers\Staff\Api;

use App\Models\DocumentReceipt;
use App\Services\DocumentReceiptService;
use App\Http\Controllers\Controller;
use App\Http\Resources\Staff\DocumentReceipt\ShowResource;
use Illuminate\Http\Request;
use Gate;

class DocumentReceiptController extends Controller
{
    public function __construct(DocumentReceiptService $documentReceiptService)
    {
        $this->documentReceiptService = $documentReceiptService;
    }

    /**
     * 見積書データを取得
     */
    public function show($agencyAccount, $id)
    {
        $documentReceipt = $this->documentReceiptService->find($id, [], true); // 論理削除も取得

        // 認可チェック
        $response = Gate::inspect('view', [$documentReceipt]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        return new ShowResource($documentReceipt);
    }
}
