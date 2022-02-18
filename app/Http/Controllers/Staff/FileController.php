<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Services\DocumentPdfService;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function __construct(DocumentPdfService $documentPdfService)
    {
        $this->documentPdfService = $documentPdfService;
    }

    /**
     * 帳票PDFの出力 or ダウンロード
     *
     * @param string $category 書類カテゴリ（←このパラメータは特に使っていない）
     * @param string $documentHashId 書類ID（ハッシュ）
     * @param string $output 出力タイプ（ダウンロードorインライン）。download or inline
     */
    public function documentPdf(Request $request, string $agencyAccount, string $category, string $documentHashId, string $output = 'inline')
    {
        $documentDecodeId = \Hashids::decode($documentHashId)[0] ?? null;
        // document_pdfsレコードを取得
        $documentPdf = $this->documentPdfService->find((int)$documentDecodeId);

        if (!$documentPdf) {
            abort(404);
        }

        // 権限チェック
        $response = \Gate::inspect('view', $documentPdf->documentable);
        if (!$response->allowed()) {
            abort(403);
        }

        if ($documentPdf->file_name) {
            $storage = \Storage::disk('s3');
            $fileName = sprintf("document%s.pdf", date('YmdHi'));

            if ($output==='inline') {
                $file = $storage->get($documentPdf->file_name);
                return response($file, 200)
                        ->header('Content-Type', 'application/pdf')
                        ->header('Content-Disposition', 'inline; filename="' . $fileName . '"');
            } elseif ($output==='download') {
                return $storage->download($documentPdf->file_name, $fileName);
            }
        }
    }
}
