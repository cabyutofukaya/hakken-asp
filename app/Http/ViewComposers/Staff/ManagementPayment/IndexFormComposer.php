<?php
namespace App\Http\ViewComposers\Staff\ManagementPayment;

use App\Services\StaffService;
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
        UserCustomItemService $userCustomItemService,
        StaffService $staffService
    ) {
        $this->userCustomItemService = $userCustomItemService;
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
            return $row->code !== config('consts.user_custom_items.CODE_MANAGEMENT_DEPOSIT_METHOD');// 入金方法は不要なので除いておく
        });

        // カスタム項目。表示位置毎に値をセット
        $customFields = [
            config('consts.user_custom_items.POSITION_MANAGEMENT_COMMON_FIELD') => $userCustomItems->where('display_position', config('consts.user_custom_items.POSITION_MANAGEMENT_COMMON_FIELD')), // 共通カスタム
            config('consts.user_custom_items.POSITION_PAYMENT_MANAGEMENT') => $userCustomItems->where('display_position', config('consts.user_custom_items.POSITION_PAYMENT_MANAGEMENT')), // 出金管理
        ];

        $searchParam = [];// 検索パラメータ
        foreach (array_merge(['payable_number','status','reserve_number','supplier_name','item_name','item_code','last_manager_id','payment_date_from','payment_date_to', 'search_option_open'], $userCustomItems->pluck('key')->all()) as $p) { // 基本パラメータ + カスタム項目パラメータ
            $searchParam[$p] = Request::get($p);
        }

        // 検索フォーム用
        $formSelects = [
            'staffs' => ['' => 'すべて'] + $this->staffService->getIdNameSelect($agencyAccount, true), // 自社スタッフ。削除スタッフ含む
            'statuses' => [''=>'すべて', config('consts.account_payable_details.STATUS_UNPAID') => '未払いのみ'],
            'userCustomItems' => $userCustomItems,
        ];

        // モーダル用
        $modalFormSelects = [
            'staffs' => ['' => '---'] + $this->staffService->getIdNameSelect($agencyAccount, false), // 自社スタッフ。未削除スタッフのみ
        ];

        // 出金方法のカスタムキー
        $withdrawalMethodKey = $userCustomItems->firstWhere('code', config('consts.user_custom_items.CODE_MANAGEMENT_WITHDRAWAL_METHOD')) ? $userCustomItems->firstWhere('code', config('consts.user_custom_items.CODE_MANAGEMENT_WITHDRAWAL_METHOD'))->toArray()['key'] : null;

        $consts = [
            'statusVals' => config('consts.account_payable_details.STATUS_LIST'),
            // カスタムフィールド表示位置
            'customFieldPositions' => [
                'management_common' => config('consts.user_custom_items.POSITION_MANAGEMENT_COMMON_FIELD'),
                'payment_management' => config('consts.user_custom_items.POSITION_PAYMENT_MANAGEMENT')
            ],
            // カスタム項目管理コード
            'customFieldCodes' => [
                'withdrawal_method' => config('consts.user_custom_items.CODE_MANAGEMENT_WITHDRAWAL_METHOD'), // 出金方法
            ],
            // 出金方法カスタムキー
            'withdrawalMethodKey' => $withdrawalMethodKey,
            'managerId' => $my->id, // 自身のstaff ID。支払一括処理の担当者IDなどに使用
        ];

        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agencyAccount);

        $view->with(compact('formSelects', 'modalFormSelects', 'searchParam', 'consts', 'customFields', 'customCategoryCode', 'jsVars'));
    }
}
