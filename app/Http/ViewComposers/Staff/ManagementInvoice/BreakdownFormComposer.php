<?php
namespace App\Http\ViewComposers\Staff\ManagementInvoice;

use App\Services\StaffService;
use App\Services\UserCustomItemService;
use App\Traits\ConstsTrait;
use App\Traits\JsConstsTrait;
use App\Traits\UserCustomItemTrait;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use Request;


/**
 * 一括請求内訳一覧に使う選択項目などを提供するViewComposer
 */
class BreakdownFormComposer
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
        $data = $view->getData(); // controllerにセットされたデータを取得

        $reserveBundleInvoiceId = Arr::get($data, 'reserveBundleInvoiceId');
        $reserveBundleInvoice = Arr::get($data, 'reserveBundleInvoice');

        //////////////////////////////////

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

        // カスタム項目。表示位置毎に値をセット
        $customFields = [
            config('consts.user_custom_items.POSITION_MANAGEMENT_COMMON_FIELD') => $userCustomItems->where('display_position', config('consts.user_custom_items.POSITION_MANAGEMENT_COMMON_FIELD')), // 共通カスタム
            config('consts.user_custom_items.POSITION_INVOICE_MANAGEMENT') => $userCustomItems->where('display_position', config('consts.user_custom_items.POSITION_INVOICE_MANAGEMENT')), // 請求管理
        ];

        // 一覧表示用
        $formSelects = [
            'staffs' => $this->staffService->getIdNameSelect($agencyAccount, true) // 削除スタッフ含む
        ];

        // モーダル用
        $modalFormSelects = [
            'staffs' => ['' => '---'] + $this->staffService->getIdNameSelect($agencyAccount, false), // 自社スタッフ。未削除スタッフのみ
        ];

        // 入金方法のカスタムキー
        $depositMethodKey = $userCustomItems->firstWhere('code', config('consts.user_custom_items.CODE_MANAGEMENT_DEPOSIT_METHOD')) ? $userCustomItems->firstWhere('code', config('consts.user_custom_items.CODE_MANAGEMENT_DEPOSIT_METHOD'))->toArray()['key'] : null;

        $consts = [
            'statusVals' => config('consts.reserve_invoices.STATUS_LIST'),
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
            'listTypes'=> config('consts.reserve_invoices.LIST_TYPE_LIST')
        ];

        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agencyAccount);

        $view->with(compact('customCategoryCode', 'reserveBundleInvoiceId', 'reserveBundleInvoice', 'depositMethodKey', 'formSelects', 'modalFormSelects', 'consts', 'customFields', 'jsVars'));
    }
}
