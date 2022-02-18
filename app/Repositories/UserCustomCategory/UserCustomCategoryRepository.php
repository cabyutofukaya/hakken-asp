<?php
namespace App\Repositories\UserCustomCategory;

use App\Models\UserCustomCategory;
use Illuminate\Support\Collection;

class UserCustomCategoryRepository implements UserCustomCategoryRepositoryInterface
{
    /**
    * @param object $userCustomItem
    */
    public function __construct(UserCustomCategory $userCustomCategory)
    {
        $this->userCustomCategory = $userCustomCategory;
    }

    public function find(int $id): UserCustomCategory
    {
        return $this->userCustomCategory->findOrFail($id);
    }

    public function all(array $with, string $sort, string $direction): Collection
    {
        $query = $this->userCustomCategory;
        $query = $with ? $query->with($with) : $query;

        return $query->orderBy($sort, $direction)->get();
    }

    /**
     * 当該タイプ(text,list,date)を有するカテゴリリストを取得
     *
     * @param string $type 項目タイプ
     * @return array
     */
    public function getListForCategoryItemType(string $type): Collection
    {
        return $this->userCustomCategory->whereHas('user_custom_category_items', function ($q) use ($type) {
            $q->where('type', $type);
        })->orderBy('seq', 'asc')->get();
    }

    public function findByCode(string $code) : ?UserCustomCategory
    {
        return $this->userCustomCategory->where('code', $code)->first();
    }
}
