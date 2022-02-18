<?php

namespace App\Services;

use App\Models\DocumentPdf;
use App\Repositories\DocumentPdf\DocumentPdfRepository;
use Illuminate\Http\Request;

class DocumentPdfService
{
    public function __construct(DocumentPdfRepository $documentPdfRepository)
    {
        $this->documentPdfRepository = $documentPdfRepository;
    }

    /**
     * idから一件取得
     */
    public function find(int $id, array $with = [], array $select=[], bool $getDeleted = false) : ?DocumentPdf
    {
        return $this->documentPdfRepository->find($id, $with, $select, $getDeleted);
    }

}
