<?php

namespace App\Repositories\DocumentPdf;

use App\Models\DocumentPdf;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface DocumentPdfRepositoryInterface
{
  public function find(int $id, array $with = [], array $select=[], bool $getDeleted = false) : DocumentPdf;
}
