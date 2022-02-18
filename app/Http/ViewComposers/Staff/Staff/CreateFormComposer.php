<?php
namespace App\Http\ViewComposers\Staff\Staff;

use Illuminate\Support\Arr;
use Illuminate\View\View;
use Lang;
use App\Services\UserCustomCategoryService;
use App\Services\AgencyRoleService;
use App\Services\StaffService;
use App\Services\UserCustomItemService;
use App\Traits\UserCustomItemTrait;

/**
 * スタッフ作成フォームに使う選択項目などを提供するViewComposer
 */
class CreateFormComposer
{
    use UserCustomItemTrait;

    public function __construct(UserCustomCategoryService $userCustomCategoryService, AgencyRoleService $agencyRoleService, StaffService $staffService, UserCustomItemService $userCustomItemService)
    {
        $this->userCustomCategoryService = $userCustomCategoryService;
        $this->agencyRoleService = $agencyRoleService;
        $this->staffService = $staffService;
        $this->userCustomItemService = $userCustomItemService;
    }
    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        // スタッフ情報のカスタムカテゴリ識別コード
        $customCategoryCode = config('consts.user_custom_categories.CUSTOM_CATEGORY_USER');

        $agencyAccount = request()->agencyAccount;

        //////////////// form初期値を設定 ////////////////
        // 基本項目
        $defaultValue = session()->getOldInput();
        $defaultValue['status'] = Arr::get($defaultValue, "status", config('consts.staffs.STATUS_VALID')); // 状態は初期画面でもデフォルト値が設定できるように明示的に初期化

        // カスタム項目のinput初期値をセット
        $this->getUserCustomItemsAndSetCustomFieldDefaultCreateInput(
            $defaultValue,
            $this->userCustomItemService, 
            $agencyAccount, 
            $customCategoryCode
        );

        ////////////////////////////////

        $formSelects = [
            'statuses' => get_const_item('staffs', 'status'), // 状態ステータス
        ];

        $userCustomItemTypes = config('consts.user_custom_items.CUSTOM_ITEM_LIST');

        $view->with(compact('formSelects', 'defaultValue', 'customCategoryCode', 'userCustomItemTypes'));
    }
}
