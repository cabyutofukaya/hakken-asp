<?php

namespace App\Http\Controllers\Staff\Api;

use App\Models\DocumentCommon;
use App\Services\DocumentCommonService;
use App\Http\Controllers\Controller;
use App\Http\Resources\Staff\DocumentCommon\ShowResource;
use Illuminate\Http\Request;
use Gate;

class DocumentCommonController extends Controller
{
    public function __construct(DocumentCommonService $documentCommonService)
    {
        $this->documentCommonService = $documentCommonService;
    }

    /**
     * 共通設定データを取得
     */
    public function show($agencyAccount, $id)
    {
        $documentCommon = $this->documentCommonService->find($id, [], true); // 論理削除も取得

        // 認可チェック
        $response = Gate::inspect('viewAny', [$documentCommon]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        return new ShowResource($documentCommon);
    }
}
