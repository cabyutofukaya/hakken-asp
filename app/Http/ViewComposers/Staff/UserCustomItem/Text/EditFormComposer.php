<?php
namespace App\Http\ViewComposers\Staff\UserCustomItem\Text;

use Illuminate\Support\Arr;
use Illuminate\View\View;
use Lang;
use App\Services\UserCustomCategoryService;
use App\Services\UserCustomCategoryItemService;

/**
 * カスタム項目（テキスト用）編集フォームに使う選択項目などを提供するViewComposer
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

        $inputTypes = [];
        $values = Lang::get('values.user_custom_items.input_type_text');
        foreach (config("consts.user_custom_items.INPUT_TYPE_TEXT_LIST") as $k => $v) {
            $inputTypes[$v] = Arr::get($values, $k);
        }


        $formSelects = [
            'userCustomCategories' => $this->userCustomCategoryService->getNameListForCategoryItemType($userCustomItem->type), // 当該項目種別を有するカテゴリ名一覧
            'positions' => $userCustomItem->user_custom_category_item->display_positions, // 設置場所一覧
            'positionLabel' => Arr::get($this->userCustomCategoryService->getPositionLabelListForCategoryItemType($userCustomItem->type), $userCustomItem->user_custom_category_id), // position項目のラベル
            'inputTypes' => $inputTypes
        ];

        $view->with(compact('formSelects'));
    }
}
