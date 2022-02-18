<?php
namespace App\Repositories\SubjectCategory;

use App\Models\SubjectCategory;
use Illuminate\Support\Collection;

class SubjectCategoryRepository implements SubjectCategoryRepositoryInterface
{
    public function __construct(SubjectCategory $subjectCategory)
    {
        $this->subjectCategory = $subjectCategory;
    }

    /**
     * 科目カテゴリデータを全取得
     *
     * @return Illuminate\Support\Collection
     */
    public function all(array $with=[], string $order="seq", string $direction="asc") : Collection
    {
        $query = $with ? $this->subjectCategory->with($with) : $this->subjectCategory;

        return $query->orderBy($order, $direction)->get();
    }

    /**
     * 科目カテゴリコードから当該IDを取得
     * 
     * @param string $code カテゴリコード
     * @return int
     */
    public function getIdByCode(string $code) : ?int
    {
        return $this->subjectCategory->where('code', $code)->value('id');
    }
}
