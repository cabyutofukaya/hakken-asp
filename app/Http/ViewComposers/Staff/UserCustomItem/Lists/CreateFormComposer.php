<?php
namespace App\Http\ViewComposers\Staff\UserCustomItem\Lists;

use Illuminate\Support\Arr;
use Illuminate\View\View;
use Lang;
use App\Services\UserCustomCategoryService;
use App\Services\UserCustomCategoryItemService;

/**
 * カスタム項目（リスト用）作成フォームに使う選択項目などを提供するViewComposer
 */
class CreateFormComposer
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
        $defaultUserCustomCategoryId = Arr::get($data, 'defaultUserCustomCategoryId');

        //////////////////////////////////

        // 「戻る」のパラメータに使用するカテゴリコード
        $defaultUserCustomCategoryCode = $this->userCustomCategoryService->getCodeById((int)$defaultUserCustomCategoryId);

        // 項目種別
        $type = config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST');

        $formSelects = [
            'userCustomCategories' => $this->userCustomCategoryService->getNameListForCategoryItemType($type), // 当該項目種別を有するカテゴリ名一覧
            'positions' => $this->userCustomCategoryItemService->getDisplayPositionListAtCategory($type), // カスタム項目のカテゴリ一覧＆同項目一覧
            'positionLabels' => $this->userCustomCategoryService->getPositionLabelListForCategoryItemType($type), // position項目のラベル一覧
        ];
        
        $view->with(compact('formSelects', 'defaultUserCustomCategoryCode'));
    }
}
