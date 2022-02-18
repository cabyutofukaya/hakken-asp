<?php
namespace App\Http\ViewComposers\Staff\Supplier;

use App\Services\BankService;
use App\Services\SupplierService;
use App\Services\UserCustomCategoryService;
use App\Services\UserCustomItemService;
use App\Traits\JsConstsTrait;
use Illuminate\Support\Arr;
use Illuminate\View\View;


/**
 * 編集フォームに使う選択項目などを提供するViewComposer
 */
class EditFormComposer
{
    use JsConstsTrait;

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
        $data = $view->getData(); // controllerにセットされたデータを取得
        $supplier = Arr::get($data, 'supplier');

        //////////////////////////////////

        $agencyAccount = request()->agencyAccount;

        // 「仕入れ先マスタ」のカスタムカテゴリコードを取得
        $customCategoryCode
        = config('consts.user_custom_categories.CUSTOM_CATEGORY_SUPPLIER');

        // 当該マスタに設定されたカスタム項目を取得
        $userCustomItems = $this->userCustomItemService->getByCategoryCodeForAgencyAccount(
            config('consts.user_custom_categories.CUSTOM_CATEGORY_SUPPLIER'), 
            $agencyAccount, 
            true
        );

        //////////////// form初期値を設定 ////////////////
        // 基本項目
        foreach (['code','name','reference_date','cutoff_date','payment_month','payment_day','note','supplier_account_payables'] as $f) {
            $defaultValue[$f] = old($f, data_get($supplier, $f));
        }

        if ($defaultValue['supplier_account_payables']->count()===0) { // 振込先情報が1件もない場合は空配列で初期化
            $defaultValue['supplier_account_payables'][] = [];
        }
        
        // 当該レコードに設定されたカスタム項目値
        $vSupplierCustomValues = $supplier->v_supplier_custom_values;
        foreach ($userCustomItems->pluck('key') as $key) {
            $row = $vSupplierCustomValues->firstWhere('key', $key);
            $defaultValue[$key] = old($key, Arr::get($row, 'val')); // val値をセット
        }
        ////////////////////////////////


        $bankSelectItems = array(); // 銀行データ選択リスト
        foreach ($supplier->supplier_account_payables as $i => $val) {
            if ($val->kinyu_code || $val->tenpo_code) {
                $bankSelectItems[$i] = $this->bankService->getTenpoNamesForSelectItem($val->kinyu_code, $val->tenpo_code);
            } else {
                $bankSelectItems[$i] = null;
            }
        }

        $dates = [""=>"---"] + $this->supplierService->getDateSelect();

        $formSelects = [
            'referenceDates' => [""=>"---"] + get_const_item('suppliers', 'reference_date'), // 基準日
            // 'paydays' => [""=>"---"] + get_const_item('suppliers', 'payday'), // 支払日
            'paymentMonths' => [""=>"---"] + get_const_item('suppliers', 'payment_month'), // 入金日(月)
            'cutoffDates' => $dates,
            'paymentDays' => $dates,
            'bankAccountTypes' => get_const_item('supplier_account_payables', 'account_type'), // 口座種別
            'userCustomItems' => $userCustomItems, // カスタム項目
            'bankSelectItems' => $bankSelectItems,
        ];

        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agencyAccount);

        $view->with(compact('defaultValue', 'formSelects', 'customCategoryCode', 'jsVars'));
    }
}
