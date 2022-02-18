<?php

namespace App\Services;

use App\Models\DocumentPdfInterface;

/**
 * 予約書類関連の親クラス
 */
class ReserveDocumentService
{
    /**
     * PDF作成
     *
     * @return string ファイルパス
     */
    public function createPdf(string $viewPath, $viewParam)
    {
        $fileName = sha1(uniqid(mt_rand(), true)); // ファイル名を生成
        $filePath = sprintf("%s%s.pdf", config('consts.const.UPLOAD_PRIVATE_PDF_DIR'), $fileName);
        $pdf = \PDF::loadView($viewPath, $viewParam);
        \Storage::disk('s3')->put($filePath, $pdf->output(), 'private');

        return $filePath;
    }

    /**
     * pdfファイル名をセット
     *
     * 既存のPDFを削除 -> 新しいPDF名をセット
     */
    public function setPdf(DocumentPdfInterface $reserveDocument, string $filePath, int $agencyId) : void
    {
        // 保存済みのpdf情報削除
        if ($reserveDocument->pdf) {
            \Storage::disk('s3')->delete($reserveDocument->pdf->file_name);

            $reserveDocument->pdf()->each(function ($pdf) {
                $pdf->forceDelete(); // 物理削除
            });
        }

        // pdf情報保存
        $reserveDocument->pdf()->create([
            'agency_id' => $agencyId,
            'file_name' => $filePath,
            'original_file_name' => null,
            'mime_type' => \Storage::disk('s3')->mimeType($filePath),
            'file_size' => \Storage::disk('s3')->size($filePath),
            'description' => null,
        ]);
    }
}
