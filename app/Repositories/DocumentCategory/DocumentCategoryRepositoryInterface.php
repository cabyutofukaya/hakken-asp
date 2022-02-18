<?php

namespace App\Repositories\DocumentCategory;

use Illuminate\Support\Collection;


interface DocumentCategoryRepositoryInterface
{
    public function all(array $with=[], string $order="seq", string $direction="asc") : Collection;
    public function getIdByCode(string $code) : ?int;
}
