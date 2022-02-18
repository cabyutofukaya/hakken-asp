<?php
namespace App\Repositories\DocumentCategory;

use App\Models\DocumentCategory;
use Illuminate\Support\Collection;

class DocumentCategoryRepository implements DocumentCategoryRepositoryInterface
{
    public function __construct(DocumentCategory $documentCategory)
    {
        $this->documentCategory = $documentCategory;
    }

    /**
     * 当該会社に紐づく帳票カテゴリデータを取得
     *
     * @param int $agencyId 会社ID
     * @return Illuminate\Support\Collection
     */
    public function all(array $with=[], string $order="seq", string $direction="asc") : Collection
    {
        $query = $with ? $this->documentCategory->with($with) : $this->documentCategory;

        return $query->orderBy($order, $direction)->get();
    }

    /**
     * 帳票カテゴリコードから当該IDを取得
     * 
     * @param string $code カテゴリコード
     * @return int
     */
    public function getIdByCode(string $code) : ?int
    {
        return $this->documentCategory->where('code', $code)->value('id');
    }
}
