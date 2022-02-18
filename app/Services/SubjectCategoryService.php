<?php

namespace App\Services;

use Illuminate\Support\Arr;
use App\Models\SubjectCategory;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\SubjectCategory\SubjectCategoryRepository;
use App\Traits\ConstsTrait;


class SubjectCategoryService
{
    use ConstsTrait;
    
    public function __construct(SubjectCategoryRepository $subjectCategoryRepository)
    {
        $this->subjectCategoryRepository = $subjectCategoryRepository;
    }

    /**
     * 科目カテゴリデータを取得
     *
     * @return Illuminate\Support\Collection
     */
    public function all(array $with=[], string $order="seq", string $direction="asc") : Collection
    {
        return $this->subjectCategoryRepository->all($with, $order, $direction);
    }
}
