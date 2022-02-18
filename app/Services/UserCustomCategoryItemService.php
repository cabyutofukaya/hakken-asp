<?php

namespace App\Services;

use App\Models\UserCustomCategoryItem;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use App\Repositories\UserCustomCategoryItem\UserCustomCategoryItemRepository;

class UserCustomCategoryItemService
{
    public function __construct(UserCustomCategoryItemRepository $userCustomCategoryItemRepository)
    {
        $this->userCustomCategoryItemRepository = $userCustomCategoryItemRepository;
    }

    /**
     * 該当レコードを一件取得
     *
     * @param int $userCustomCategoryItemId 項目ID
     * @return App\Models\UserCustomCategoryItem
     */
    public function find(int $userCustomCategoryItemId) : ?UserCustomCategoryItem
    {
        return $this->userCustomCategoryItemRepository->find($userCustomCategoryItemId);
    }

    public function findWhere($where) : ?UserCustomCategoryItem
    {
        return $this->userCustomCategoryItemRepository->findWhere($where);
    }

    /**
     * 当該タイプの配置箇所一覧を取得（カスタムカテゴリ毎）
     */
    public function getDisplayPositionListAtCategory($type) : array
    {
        $objs = $this->userCustomCategoryItemRepository->getDisplayPositionsForType($type);

        $res = [];
        foreach ($objs as $obj) {
            $res[$obj->user_custom_category_id] = $obj->display_positions; // カテゴリID => 配置箇所リスト
        }
        return $res;
    }
}
