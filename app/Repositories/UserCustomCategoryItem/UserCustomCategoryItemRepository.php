<?php
namespace App\Repositories\UserCustomCategoryItem;

use App\Models\UserCustomCategoryItem;
use Illuminate\Support\Collection;

class UserCustomCategoryItemRepository implements UserCustomCategoryItemRepositoryInterface
{
    /**
    * @param object $userCustomItem
    */
    public function __construct(UserCustomCategoryItem $userCustomCategoryItem)
    {
        $this->userCustomCategoryItem = $userCustomCategoryItem;
    }

    /**
     * 該当レコードを一件取得
     *
     * @param int $id 項目ID
     * @return App\Models\UserCustomCategoryItem
     */
    public function find(int $id): ?UserCustomCategoryItem
    {
        return $this->userCustomCategoryItem->find('id', $id);
    }

    public function findWhere(array $where) : ?UserCustomCategoryItem
    {
        $query = $this->userCustomCategoryItem;
        foreach ($where as $key => $val) {
            $query = $query->where($key, $val);
        }
        return $query->first();
    }

    /**
     * 当該項目種別に対応したレコードを取得
     *
     * @param string $type 項目種別
     */
    public function getDisplayPositionsForType(string $type) : Collection
    {
        return $this->userCustomCategoryItem->select('user_custom_category_id', 'display_positions')->where('type', $type)->get();
    }
}
