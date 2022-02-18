<?php
namespace App\Http\ViewComposers\Staff\UserCustomItem\Lists;

use App\Services\UserCustomCategoryItemService;
use App\Services\UserCustomCategoryService;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use Lang;

/**
 * カスタム項目（リスト用）編集フォームに使う選択項目などを提供するViewComposer
 */
class EditFormComposer
{
    public function __construct(UserCustomCategoryService $userCustomCategoryService, UserCustomCategoryItemService $userCustomCategoryItemService)
    {
        $this->userCustomCategoryService = $userCustomCategoryService;
        $this->userCustomCategoryItemService = $userCustomCategoryItemService;
    }
    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        $data = $view->getData(); // controllerにセットされたデータを取得
        $userCustomItem = Arr::get($data, 'userCustomItem');

        //////////////////////////////////

        // 項目種別
        $type = config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST');

        $formSelects = [
            'userCustomCategories' => $this->userCustomCategoryService->getNameListForCategoryItemType($type), // 当該項目種別を有するカテゴリ名一覧
            'positions' => $userCustomItem->user_custom_category_item->display_positions, // 設置場所一覧 // カスタム項目のカテゴリ一覧＆同項目一覧
            'positionLabel' => Arr::get($this->userCustomCategoryService->getPositionLabelListForCategoryItemType($type), $userCustomItem->user_custom_category_id), // position項目のラベル
        ];

        $protectList = $userCustomItem->protect_list; // 保護リスト

        $view->with(compact('formSelects', 'protectList'));
    }
}
