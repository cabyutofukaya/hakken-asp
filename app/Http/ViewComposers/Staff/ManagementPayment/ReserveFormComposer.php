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
 *一覧ページ(予約毎)に使う選択項目などを提供するViewComposer
 */
class ReserveFormComposer
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

        // カスタム項目のinput初期値をセット
        $defaultValue = [];


        $searchParam = [];// 検索パラメータ
        foreach (['status','reserve_number','manager_id','departure_date_from','departure_date_to'] as $p) {
            $searchParam[$p] = Request::get($p);
        }

        // 検索フォーム用
        $formSelects = [
            'staffs' => ['' => 'すべて'] + $this->staffService->getIdNameSelect($agencyAccount, true), // 自社スタッフ。削除スタッフ含む
            'statuses' => [''=>'すべて', config('consts.account_payable_reserves.STATUS_UNPAID') => '未払いのみ'],
        ];

        $consts = [
            'statusVals' => config('consts.account_payable_reserves.STATUS_LIST'),
        ];

        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agencyAccount);

        $view->with(compact('formSelects', 'searchParam', 'consts', 'jsVars'));
    }
}
