<?php

namespace App\Http\Controllers\Staff\Api;

use App\Models\DocumentRequest;
use App\Services\DocumentRequestService;
use App\Http\Controllers\Controller;
use App\Http\Resources\Staff\DocumentRequest\ShowResource;
use Illuminate\Http\Request;
use Gate;

class DocumentRequestController extends Controller
{
    public function __construct(DocumentRequestService $documentRequestService)
    {
        $this->documentRequestService = $documentRequestService;
    }

    /**
     * 見積書データを取得
     */
    public function show($agencyAccount, $id)
    {
        $documentRequest = $this->documentRequestService->find($id, [], true); // 論理削除も取得

        // 認可チェック
        $response = Gate::inspect('view', [$documentRequest]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        return new ShowResource($documentRequest);
    }
}
