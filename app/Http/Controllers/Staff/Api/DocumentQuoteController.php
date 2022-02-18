<?php

namespace App\Http\Controllers\Staff\Api;

use App\Models\DocumentQuote;
use App\Services\DocumentQuoteService;
use App\Http\Controllers\Controller;
use App\Http\Resources\Staff\DocumentQuote\ShowResource;
use Illuminate\Http\Request;
use Gate;

/**
 * 見積・予約確認書
 */
class DocumentQuoteController extends Controller
{
    public function __construct(DocumentQuoteService $documentQuoteService)
    {
        $this->documentQuoteService = $documentQuoteService;
    }

    /**
     * 見積・予約確認書データを取得
     */
    public function show($agencyAccount, $id)
    {
        $documentQuote = $this->documentQuoteService->find($id, [], [], true); // 論理削除も取得

        // 認可チェック
        $response = Gate::inspect('view', [$documentQuote]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        return new ShowResource($documentQuote);
    }
}
