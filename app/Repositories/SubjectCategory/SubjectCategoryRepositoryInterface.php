<?php

namespace App\Repositories\SubjectCategory;

use Illuminate\Support\Collection;

interface SubjectCategoryRepositoryInterface
{
    public function all(array $with=[], string $order="seq", string $direction="asc") : Collection;
    public function getIdByCode(string $code) : ?int;
}
