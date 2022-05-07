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
 *一覧ページに使う選択項目などを提供するViewComposer
 */
class IndexFormComposer
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

        $customCategoryCode = config('consts.user_custom_categories.CUSTOM_CATEGORY_CONSULTATION');

        // 当該会社に紐づく全カスタム項目データ(flg=true)
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
            ] // 取得カラム
        );

        $searchParam = [];// 検索パラメータ
        foreach (array_merge(['reserve_estimate_number', 'title', 'deadline_from', 'deadline_to', 'reception_date_from', 'reception_date_to', 'kind', 'departure_date_from', 'departure_date_to', 'search_option_open'], $userCustomItemDatas->pluck('key')->all()) as $p) { // 基本パラメータ + カスタム項目パラメータ
            $searchParam[$p] = Request::get($p);
        }


        // 初期入力値
        $defaultValue = [
            'status' => config('consts.agency_consultations.DEFAULT_STATUS'), // 状態
            'kind' => config('consts.agency_consultations.DEFAULT_KIND'), // 種別
        ];

        // 検索フォーム用
        $formSelects = [
            'staffs' => ['' => '-'] + $this->staffService->getIdNameSelect($agencyAccount, true), // 自社スタッフ
            'statuses' => ['' => 'すべて'] + get_const_item('agency_consultations', 'status'),
            'userCustomItemDatas' => $userCustomItemDatas,
            'kinds' => ['' => 'すべて'] + get_const_item('agency_consultations', 'kind'), // 種別
            'userCustomItems' => [
                config('consts.user_custom_items.POSITION_CONSULTATION_CUSTOM_FIELD') => $userCustomItemDatas->where('display_position', config('consts.user_custom_items.POSITION_CONSULTATION_CUSTOM_FIELD')),// 相談モーダル用。カスタム項目値ではなく"selectフォームを作成するための設定値"としてセット
            ]
        ];

        $consts = [
            // ステータス値
            'statusList' => [
                'status_reception' => config('consts.agency_consultations.STATUS_RECEPTION'),
                'status_responding' => config('consts.agency_consultations.STATUS_RESPONDING'),
                'status_completion' => config('consts.agency_consultations.STATUS_COMPLETION'),
            ],
            'applicationStepList' => [
                'application_step_draft' => config('consts.reserves.APPLICATION_STEP_DRAFT'),
                'application_step_reserve' => config('consts.reserves.APPLICATION_STEP_RESERVE'),
            ],
            'taxonomyList' => [
                'taxonomy_reserve' => config('consts.agency_consultations.TAXONOMY_RESERVE'),
                'taxonomy_person' => config('consts.agency_consultations.TAXONOMY_PERSON'),
                'taxonomy_business' => config('consts.agency_consultations.TAXONOMY_BUSINESS'),
            ],
            // 予約ページタブコード一覧
            'reserveTabCodes' => [
                'tab_basic_info' => config('consts.reserves.TAB_BASIC_INFO'),
                'tab_reserve_detail' => config('consts.reserves.TAB_RESERVE_DETAIL'),
                'tab_consultation' => config('consts.reserves.TAB_CONSULTATION'),
            ],

        ];

        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agencyAccount);

        $view->with(compact('formSelects', 'searchParam', 'consts', 'customCategoryCode', 'jsVars', 'defaultValue'));
    }
}
