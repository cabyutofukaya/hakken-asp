<?php
namespace App\Http\ViewComposers\Staff\BusinessUser;

use App\Services\UserCustomCategoryService;
use App\Services\UserCustomItemService;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use Request;

/**
 *一覧ページに使う選択項目などを提供するViewComposer
 */
class IndexFormComposer
{
    public function __construct(
        UserCustomCategoryService $userCustomCategoryService,
        UserCustomItemService $userCustomItemService
        )
    {
        $this->userCustomCategoryService = $userCustomCategoryService;
        $this->userCustomItemService = $userCustomItemService;
    }

    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        $agencyAccount = request()->agencyAccount;

        // 法人顧客に紐づく全カスタム項目データ(flg=true)
        $userCustomItemDatas = $this->userCustomItemService->getByCategoryCodeForAgencyAccount(
            config('consts.user_custom_categories.CUSTOM_CATEGORY_BUSINESS'), 
            $agencyAccount, 
            true, 
            [], 
            [
                'user_custom_items.key',
                'user_custom_items.type',
                'user_custom_items.display_position',
                'user_custom_items.name',
                'user_custom_items.code',
                'user_custom_items.input_type',
                'user_custom_items.list',
                'user_custom_items.unedit_item',
            ] // 取得カラム
        );

        $searchParam = [];// 検索パラメータ
        foreach (array_merge(['user_number', 'name', 'tel', 'search_option_open'], $userCustomItemDatas->pluck('key')->all()) as $p) { // 基本パラメータ + カスタム項目パラメータ
            $searchParam[$p] = Request::get($p);
        }

        // 「法人顧客」のカスタムカテゴリCODEを取得
        $customCategoryCode = config('consts.user_custom_categories.CUSTOM_CATEGORY_BUSINESS');

        $formSelects = [
            'userCustomItemDatas' => $userCustomItemDatas,
            'searchParam' => $searchParam,
        ];

        $statusList = config("consts.business_users.STATUS_LIST");

        $view->with(compact('searchParam', 'formSelects', 'customCategoryCode', 'statusList'));
    }
}
