<?php
namespace App\Http\ViewComposers\Staff\Web\ReserveEstimate;

use App\Services\UserCustomCategoryService;
use App\Services\UserCustomItemService;
use App\Traits\JsConstsTrait;
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
            ]// 取得カラム
        );
        // 「予約ステータス」のカスタム項目を除去。予約/見積ステータスのみイレギュラー項目
        $userCustomItemDatas = $userCustomItemDatas->filter(function ($row, $key) {
            return $row['code'] !== config('consts.user_custom_items.CODE_APPLICATION_RESERVE_STATUS');
        });

        foreach (array_merge(['record_number', 'departure_date', 'return_date','departure', 'destination', 'applicant', 'representative', 'search_option_open'], $userCustomItemDatas->pluck('key')->all()) as $p) { // 基本パラメータ + カスタム項目パラメータ
            $searchParam[$p] = Request::get($p);
        }

        $formSelects = [
            'userCustomItemDatas' => $userCustomItemDatas,
            'searchParam' => $searchParam, // 検索パラメータ
        ];

        $consts= [
            'estimateStatuses' => config('consts.web_reserve_exts.ESTIMATE_STATUS_LIST'),
            'senderTypes' => config('consts.web_online_schedules.SENDER_TYPE_LIST'),
            'onlineRequestStatuses' => config('consts.web_online_schedules.ONLINE_REQUEST_STATUS_LIST'),
        ];


        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agencyAccount);
        
        $view->with(compact('searchParam', 'formSelects', 'customCategoryCode', 'jsVars', 'consts'));
    }
}
