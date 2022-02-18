<?php
namespace App\Http\ViewComposers\Staff\UserCustomItem;

use Illuminate\Support\Arr;
use Illuminate\View\View;
use Lang;
use App\Services\UserCustomCategoryService;

/**
 * カスタム項目一覧ページで使う選択項目などを提供するViewComposer
 */
class IndexComposer
{
    public function __construct(UserCustomCategoryService $userCustomCategoryService)
    {
        $this->userCustomCategoryService = $userCustomCategoryService;
    }
    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        $defaultOpenTab = request()->get('tab', config('consts.user_custom_categories.CUSTOM_CATEGORY_PERSON')); // アクティブに設定するタブ

        $formSelects = [
            'userCustomCategoryDatas' => $this->userCustomCategoryService->all(['user_custom_category_items.user_custom_items_for_agency']), // カスタム項目のカテゴリ一覧を取得
        ];

        $view->with(compact('formSelects', 'defaultOpenTab'));
    }
}
