<?php
namespace App\Http\ViewComposers\Staff\Consultation;

use App\Services\UserCustomCategoryService;
use App\Services\UserCustomItemService;
use App\Services\AccountPayableDetailService;
use App\Traits\UserCustomItemTrait;
use App\Traits\ConstsTrait;
use App\Services\StaffService;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use App\Traits\JsConstsTrait;
use Request;

/**
 * メッセージ一覧ページに使う選択項目などを提供するViewComposer
 */
class MessageFormComposer
{
    use UserCustomItemTrait, ConstsTrait, JsConstsTrait;

    public function __construct(
        AccountPayableDetailService $accountPayableDetailService,
        UserCustomCategoryService $userCustomCategoryService,
        UserCustomItemService $userCustomItemService,
        StaffService $staffService
    ) {
        $this->userCustomCategoryService = $userCustomCategoryService;
        $this->userCustomItemService = $userCustomItemService;
        $this->accountPayableDetailService = $accountPayableDetailService;
        $this->staffService = $staffService;
    }

    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        $agencyAccount = request()->agencyAccount;
        
        $searchParam = [];// 検索パラメータ
        foreach (['record_number', 'message_log', 'reserve_status', 'application_date_from', 'application_date_to', 'received_at_from', 'received_at_to', 'departure_date_from', 'departure_date_to'] as $p) { 
            $searchParam[$p] = Request::get($p);
        }

        /**
         * 予約情報のカスタム項目(ステータス)を取得
         */

        // 予約ステータス
        $reserveStatusItem = $this->userCustomItemService->findByCodeForAgency(auth('staff')->user()->agency_id, config('consts.user_custom_items.CODE_APPLICATION_RESERVE_STATUS'), ['list'], true);
        // 見積ステータス
        $estimateStatusItem = $this->userCustomItemService->findByCodeForAgency(auth('staff')->user()->agency_id, config('consts.user_custom_items.CODE_APPLICATION_ESTIMATE_STATUS'), ['list'], true);
        // 両ステータスを結合して重複削除
        $statuses = array_unique(array_merge($reserveStatusItem->list, $estimateStatusItem->list));

        // 検索フォーム用
        $formSelects = [
            'statuses' => $statuses,
        ];

        $consts = [
        ];

        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agencyAccount);

        $view->with(compact('formSelects', 'searchParam', 'consts', 'jsVars'));
    }
}
