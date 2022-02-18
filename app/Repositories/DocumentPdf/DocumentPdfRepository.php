<?php
namespace App\Repositories\DocumentPdf;

use App\Models\DocumentPdf;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Storage;

class DocumentPdfRepository implements DocumentPdfRepositoryInterface
{
    /**
    * @param object $documentPdf
    */
    public function __construct(DocumentPdf $documentPdf)
    {
        $this->documentPdf = $documentPdf;
    }

    public function find(int $id, array $with = [], array $select=[], bool $getDeleted = false) : DocumentPdf
    {
        $query = $this->documentPdf;
        $query = $select ? $query->select($select) : $query;
        $query = $with ? $query->with($with) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        return $query->findOrFail($id);
    }

}
