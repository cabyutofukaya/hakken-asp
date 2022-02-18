<?php
namespace App\Http\ViewComposers\Staff\ReserveEstimate;

use App\Services\UserCustomCategoryService;
use App\Services\UserCustomItemService;
use App\Traits\JsConstsTrait;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use Request;

/**
 * 見積一覧ページに使う選択項目などを提供するViewComposer
 */
class EstimateIndexFormComposer
{
    use JsConstsTrait;

    public function __construct(
        UserCustomCategoryService $userCustomCategoryService,
        UserCustomItemService $userCustomItemService
    ) {
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

        // 「予約・見積情報」のカスタムカテゴリCode
        $customCategoryCode = config('consts.user_custom_categories.CUSTOM_CATEGORY_RESERVE');

        // 予約・見積情報に紐づく全カスタム項目データ(flg=true)
        $userCustomItemDatas = $this->userCustomItemService->getByCategoryCodeForAgencyAccount(
            $customCategoryCode,
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
            ], // 取得カラム
            [],
            [
                'user_custom_items.code' => config('consts.user_custom_items.CODE_APPLICATION_RESERVE_STATUS')
            ] // 否定パラメータ。「予約ステータス」のカスタム項目を除去
        );


        $searchParam = [];// 検索パラメータ
        foreach (array_merge(['estimate_number', 'departure_date', 'return_date', 'departure', 'destination', 'applicant', 'representative', 'search_option_open'], $userCustomItemDatas->pluck('key')->all()) as $p) { // 基本パラメータ + カスタム項目パラメータ
            $searchParam[$p] = Request::get($p);
        }

        $formSelects = [
            'userCustomItemDatas' => $userCustomItemDatas,
            'searchParam' => $searchParam,
        ];

        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agencyAccount);

        $view->with(compact('searchParam', 'formSelects', 'customCategoryCode', 'jsVars'));
    }
}
