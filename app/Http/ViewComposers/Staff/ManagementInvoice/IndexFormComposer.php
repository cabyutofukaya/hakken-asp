<?php
namespace App\Http\ViewComposers\Staff\ManagementInvoice;

use App\Services\AccountPayableDetailService;
use App\Services\StaffService;
use App\Services\UserCustomCategoryService;
use App\Services\UserCustomItemService;
use App\Traits\ConstsTrait;
use App\Traits\JsConstsTrait;
use App\Traits\UserCustomItemTrait;
use Illuminate\Support\Arr;
use Illuminate\View\View;
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
        $my = auth("staff")->user();
        $agencyId = $my->agency_id;
        $agencyAccount = $my->agency->account;


        $customCategoryCode = config('consts.user_custom_categories.CUSTOM_CATEGORY_MANAGEMENT');

        // カスタム項目のinput初期値をセット
        $defaultValue = [];
        $userCustomItems = $this->getUserCustomItemsAndSetCustomFieldDefaultCreateInput(
            $defaultValue,
            $this->userCustomItemService,
            $agencyAccount,
            $customCategoryCode
        );

        $userCustomItems = $userCustomItems->filter(function ($row, $key) {
            return $row->code !== config('consts.user_custom_items.CODE_MANAGEMENT_WITHDRAWAL_METHOD');// 出金方法は不要なので除いておく
        });

        // カスタム項目。表示位置毎に値をセット
        $customFields = [
            config('consts.user_custom_items.POSITION_MANAGEMENT_COMMON_FIELD') => $userCustomItems->where('display_position', config('consts.user_custom_items.POSITION_MANAGEMENT_COMMON_FIELD'))->toArray(), // 共通カスタム
            config('consts.user_custom_items.POSITION_INVOICE_MANAGEMENT') => $userCustomItems->where('display_position', config('consts.user_custom_items.POSITION_INVOICE_MANAGEMENT'))->toArray(), // 請求管理
        ];

        $searchParam = [];// 検索パラメータ
        foreach (array_merge(['status','reserve_number','applicant_name','last_manager_id','issue_date_from','issue_date_to','payment_deadline_from','payment_deadline_to','search_option_open'], $userCustomItems->pluck('key')->all()) as $p) { // 基本パラメータ + カスタム項目パラメータ
            $searchParam[$p] = Request::get($p);
        }

        // 検索フォーム用
        $formSelects = [
            'staffs' => ['' => 'すべて'] + $this->staffService->getIdNameSelect($agencyAccount, true), // 自社スタッフ。削除スタッフ含む
            'statuses' => ['' => 'すべて'] + get_const_item('management_invoices', 'status'),
            'userCustomItems' => $userCustomItems,
        ];

        // モーダル用
        $modalFormSelects = [
            'staffs' => ['' => '---'] + $this->staffService->getIdNameSelect($agencyAccount, false), // 自社スタッフ。未削除スタッフのみ
        ];

        // 入金方法のカスタムキー
        $depositMethodKey = $userCustomItems->firstWhere('code', config('consts.user_custom_items.CODE_MANAGEMENT_DEPOSIT_METHOD')) ? $userCustomItems->firstWhere('code', config('consts.user_custom_items.CODE_MANAGEMENT_DEPOSIT_METHOD'))->toArray()['key'] : null;

        $consts = [
            'statusVals' => config('consts.v_reserve_invoices.STATUS_LIST'),
            // カスタムフィールド表示位置
            'customFieldPositions' => [
                'management_common' => config('consts.user_custom_items.POSITION_MANAGEMENT_COMMON_FIELD'),
                'invoice_management' => config('consts.user_custom_items.POSITION_INVOICE_MANAGEMENT')
            ],
            // カスタム項目管理コード
            'customFieldCodes' => [
                'deposit_method' => config('consts.user_custom_items.CODE_MANAGEMENT_DEPOSIT_METHOD'), // 入金方法
            ],
            // 入金方法カスタムキー
            'depositMethodKey' => $depositMethodKey,
            'managerId' => $my->id, // 自身のstaff ID。支払一括処理の担当者IDなどに使用
            // リスト種別
            'listTypes'=> config('consts.v_reserve_invoices.LIST_TYPE_LIST')
        ];

        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agencyAccount);

        $view->with(compact('formSelects', 'depositMethodKey', 'modalFormSelects', 'searchParam', 'consts', 'customFields', 'customCategoryCode', 'jsVars'));
    }
}
