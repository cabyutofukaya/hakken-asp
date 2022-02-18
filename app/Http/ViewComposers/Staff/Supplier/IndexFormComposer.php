<?php
namespace App\Http\ViewComposers\Staff\Supplier;

use App\Services\UserCustomCategoryService;
use App\Services\UserCustomItemService;
use App\Traits\JsConstsTrait;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use Request;

/**
 *一覧ページに使う選択項目などを提供するViewComposer
 */
class IndexFormComposer
{
    use JsConstsTrait;
    
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

        // 当該会社に紐づく全カスタム項目データ(flg=true)
        $userCustomItemDatas = $this->userCustomItemService->getByCategoryCodeForAgencyAccount(
            config('consts.user_custom_categories.CUSTOM_CATEGORY_SUPPLIER'), 
            $agencyAccount, 
            true
        );


        $searchParam = [];// 検索パラメータ
        foreach (array_merge(['code', 'name', 'search_option_open'], $userCustomItemDatas->pluck('key')->all()) as $p) { // 基本パラメータ + カスタム項目パラメータ
            $searchParam[$p] = Request::get($p);
        }

        // 「仕入先マスタ」のカスタムカテゴリCodeを取得
        $customCategoryCode = config('consts.user_custom_categories.CUSTOM_CATEGORY_SUPPLIER');

        $formSelects = [
            'userCustomItemDatas' => $userCustomItemDatas,
            'searchParam' => $searchParam,
        ];

        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agencyAccount);

        $view->with(compact('searchParam', 'formSelects', 'customCategoryCode', 'jsVars'));
    }
}
