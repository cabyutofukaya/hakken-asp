<?php

namespace App\Http\Controllers\Staff\Api;

use App\Models\DocumentRequestAll;
use App\Services\DocumentRequestAllService;
use App\Http\Controllers\Controller;
use App\Http\Resources\Staff\DocumentRequestAll\ShowResource;
use Illuminate\Http\Request;
use Gate;

class DocumentRequestAllController extends Controller
{
    public function __construct(DocumentRequestAllService $documentRequestAllService)
    {
        $this->documentRequestAllService = $documentRequestAllService;
    }

    /**
     * 見積書データを取得
     */
    public function show($agencyAccount, $id)
    {
        $documentRequestAll = $this->documentRequestAllService->find($id, [], true); // 論理削除も取得

        // 認可チェック
        $response = Gate::inspect('view', [$documentRequestAll]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        return new ShowResource($documentRequestAll);
    }
}
