<?php

namespace App\Http\Controllers\Staff\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Staff\Document\DocumentCommonResource;
use App\Http\Resources\Staff\Document\DocumentQuoteResource;
use App\Http\Resources\Staff\Document\DocumentReceiptResource;
use App\Http\Resources\Staff\Document\DocumentRequestAllResource;
use App\Http\Resources\Staff\Document\DocumentRequestResource;
use App\Models\DocumentCommon;
use App\Models\DocumentQuote;
use App\Models\DocumentReceipt;
use App\Models\DocumentRequest;
use App\Models\DocumentRequestAll;
use App\Services\DocumentCommonService;
use App\Services\DocumentQuoteService;
use App\Services\DocumentReceiptService;
use App\Services\DocumentRequestAllService;
use App\Services\DocumentRequestService;
use Gate;
use Hashids;
use Log;
use Illuminate\Http\Request;

/**
 * 帳票設定用API
 */
class DocumentController extends Controller
{
    public function __construct(DocumentCommonService $documentCommonService, DocumentQuoteService $documentQuoteService, DocumentRequestService $documentRequestService, DocumentRequestAllService $documentRequestAllService, DocumentReceiptService $documentReceiptService)
    {
        $this->documentCommonService = $documentCommonService;
        $this->documentQuoteService = $documentQuoteService;
        $this->documentReceiptService = $documentReceiptService;
        $this->documentRequestAllService = $documentRequestAllService;
        $this->documentRequestService = $documentRequestService;
    }
    
    /**
     * 各種ドキュメント一覧
     *
     * @param string $agencyAccount 会社アカウント
     * @param string $code 管理コード
     */
    public function index($agencyAccount, $code)
    {
        $limit = request()->get("per_page", 10);

        if ($code === config('consts.document_categories.DOCUMENT_CATEGORY_COMMON')) { // 共通設定
            // 認可チェック
            $response = Gate::inspect('viewAny', [new DocumentCommon]);
            if (!$response->allowed()) {
                abort(403, $response->message());
            }

            return DocumentCommonResource::collection($this->documentCommonService->paginateByAgencyAccount($agencyAccount, $limit));
        } elseif ($code === config('consts.document_categories.DOCUMENT_CATEGORY_QUOTE')) { // 見積/予約確認書
            // 認可チェック
            $response = Gate::inspect('viewAny', [new DocumentQuote]);
            if (!$response->allowed()) {
                abort(403, $response->message());
            }

            return DocumentQuoteResource::collection($this->documentQuoteService->paginateByAgencyAccount($agencyAccount, $limit));
        } elseif ($code === config('consts.document_categories.DOCUMENT_CATEGORY_REQUEST')) { // 請求書設定
            // 認可チェック
            $response = Gate::inspect('viewAny', [new DocumentRequest]);
            if (!$response->allowed()) {
                abort(403, $response->message());
            }

            return DocumentRequestResource::collection($this->documentRequestService->paginateByAgencyAccount($agencyAccount, $limit));
        } elseif ($code === config('consts.document_categories.DOCUMENT_CATEGORY_REQUEST_ALL')) { // 請求書一括設定
            // 認可チェック
            $response = Gate::inspect('viewAny', [new DocumentRequestAll]);
            if (!$response->allowed()) {
                abort(403, $response->message());
            }

            return DocumentRequestAllResource::collection($this->documentRequestAllService->paginateByAgencyAccount($agencyAccount, $limit));
        } elseif ($code === config('consts.document_categories.DOCUMENT_CATEGORY_RECEIPT')) { // 領収書設定
            // 認可チェック
            $response = Gate::inspect('viewAny', [new DocumentReceipt]);
            if (!$response->allowed()) {
                abort(403, $response->message());
            }

            return DocumentReceiptResource::collection($this->documentReceiptService->paginateByAgencyAccount($agencyAccount, $limit));
        }
        abort(404);
    }

    /**
     * 各種ドキュメント削除
     * 
     * @param string $agencyAccount 会社アカウント
     * @param string $code 管理コード
     * @param string $id 設定ID
     */
    public function destroy($agencyAccount, $code, $encodeId)
    {
        $decodeId = Hashids::decode($encodeId)[0] ?? null;

        if ($code === config('consts.document_categories.DOCUMENT_CATEGORY_COMMON')) { // 共通設定
            $documentCommon = $this->documentCommonService->find((int)$decodeId);

            if (!$documentCommon) {
                return response("データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。", 404);
            }
    
            // 認可チェック
            $response = Gate::inspect('delete', [$documentCommon]);
            if (!$response->allowed()) {
                abort(403, $response->message());
            }

            if ($this->documentCommonService->delete($documentCommon->id, true)) { // 論理削除
                return response('', 200);
            }
        } elseif ($code === config('consts.document_categories.DOCUMENT_CATEGORY_QUOTE')) { // 見積/予約確認書
            $documentQuote = $this->documentQuoteService->find((int)$decodeId);

            if (!$documentQuote) {
                return response("データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。", 404);
            }

            // 認可チェック
            $response = Gate::inspect('delete', [$documentQuote]);
            if (!$response->allowed()) {
                abort(403, $response->message());
            }

            if ($this->documentQuoteService->delete($documentQuote->id, true)) { // 論理削除
                return response('', 200);
            }
        } elseif ($code === config('consts.document_categories.DOCUMENT_CATEGORY_REQUEST')) { // 請求書設定
            $documentRequest = $this->documentRequestService->find((int)$decodeId);

            if (!$documentRequest) {
                return response("データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。", 404);
            }

            // 認可チェック
            $response = Gate::inspect('delete', [$documentRequest]);
            if (!$response->allowed()) {
                abort(403, $response->message());
            }

            if ($this->documentRequestService->delete($documentRequest->id, true)) { // 論理削除
                return response('', 200);
            }
        } elseif ($code === config('consts.document_categories.DOCUMENT_CATEGORY_REQUEST_ALL')) { // 請求書一括設定
            $documentRequestAll = $this->documentRequestAllService->find((int)$decodeId);

            if (!$documentRequestAll) {
                return response("データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。", 404);
            }

            // 認可チェック
            $response = Gate::inspect('delete', [$documentRequestAll]);
            if (!$response->allowed()) {
                abort(403, $response->message());
            }

            if ($this->documentRequestAllService->delete($documentRequestAll->id, true)) { // 論理削除
                return response('', 200);
            }
        } elseif ($code === config('consts.document_categories.DOCUMENT_CATEGORY_RECEIPT')) { // 領収書設定
            $documentReceipt = $this->documentReceiptService->find((int)$decodeId);

            if (!$documentReceipt) {
                return response("データが見つかりません。もう一度編集する前に、画面を再読み込みして最新情報を表示してください。", 404);
            }

            // 認可チェック
            $response = Gate::inspect('delete', [$documentReceipt]);
            if (!$response->allowed()) {
                abort(403, $response->message());
            }

            if ($this->documentReceiptService->delete($documentReceipt->id, true)) { // 論理削除
                return response('', 200);
            }
        }


        abort(500);
    }
}
