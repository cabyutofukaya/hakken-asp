<?php
namespace App\Http\ViewComposers\Staff\Supplier;

use App\Services\BankService;
use App\Services\SupplierService;
use App\Services\UserCustomCategoryService;
use App\Services\UserCustomItemService;
use App\Traits\JsConstsTrait;
use App\Traits\UserCustomItemTrait;
use Illuminate\View\View;

/**
 * 作成フォームに使う選択項目などを提供するViewComposer
 */
class CreateFormComposer
{
    use UserCustomItemTrait, JsConstsTrait;

    public function __construct(SupplierService $supplierService, BankService $bankService, UserCustomItemService $userCustomItemService, UserCustomCategoryService $userCustomCategoryService)
    {
        $this->supplierService = $supplierService;
        $this->bankService = $bankService;
        $this->userCustomItemService = $userCustomItemService;
        $this->userCustomCategoryService = $userCustomCategoryService;
    }

    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        $agencyAccount = request()->agencyAccount;

        // 「仕入れ先マスタ」のカスタムカテゴリコードを取得
        $customCategoryCode
        = config('consts.user_custom_categories.CUSTOM_CATEGORY_SUPPLIER');


        $defaultValue = session()->getOldInput();

        // カスタム項目を取得しつつ、カスタム項目のinput初期値をセット
        $userCustomItems = $this->getUserCustomItemsAndSetCustomFieldDefaultCreateInput(
            $defaultValue,
            $this->userCustomItemService,
            $agencyAccount,
            config('consts.user_custom_categories.CUSTOM_CATEGORY_SUPPLIER')
        );

        $dates = ["" => "---"] + $this->supplierService->getDateSelect();

        $formSelects = [
            'referenceDates' => ["" => "---"] + get_const_item('suppliers', 'reference_date'), // 基準日
            // 'paydays' => [""=>"---"] + get_const_item('suppliers', 'payday'), // 支払日
            'paymentMonths' => ["" => "---"] + get_const_item('suppliers', 'payment_month'), // 入金日(月)
            'cutoffDates' => $dates,
            'paymentDays' => $dates,
            'bankAccountTypes' => get_const_item('supplier_account_payables', 'account_type'), // 口座種別
            'userCustomItems' => $userCustomItems, // カスタム項目
        ];

        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agencyAccount);

        $view->with(compact('defaultValue', 'formSelects', 'customCategoryCode', 'jsVars'));
    }
}
