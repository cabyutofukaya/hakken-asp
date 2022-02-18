<?php
namespace App\Http\ViewComposers\Staff\Staff;

use App\Services\StaffService;
use App\Services\AgencyRoleService;
use App\Services\UserCustomItemService;
use App\Services\UserCustomCategoryService;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use Lang;
use Request;

/**
 * スタッフ編集フォームに使う選択項目などを提供するViewComposer
 */
class IndexFormComposer
{
    public function __construct(StaffService $staffService, AgencyRoleService $agencyRoleService, UserCustomItemService $userCustomItemService, UserCustomCategoryService $userCustomCategoryService)
    {
        $this->staffService = $staffService;
        $this->agencyRoleService = $agencyRoleService;
        $this->userCustomItemService = $userCustomItemService;
        $this->userCustomCategoryService = $userCustomCategoryService;
    }

    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        $agencyId = auth('staff')->user()->agency->id;

        // 当該会社に紐づく全カスタム項目データ(flg=true)
        $userCustomItemDatas = $this->userCustomItemService->getByCategoryCodeForAgencyAccount(
            config('consts.user_custom_categories.CUSTOM_CATEGORY_USER'), 
            auth('staff')->user()->agency->account, 
            true
        );

        $searchParam = [];// 検索パラメータ
        foreach (array_merge(['account', 'name', 'agency_role_id', 'email', 'status', 'search_option_open'], $userCustomItemDatas->pluck('key')->all()) as $p) { // 基本パラメータ + カスタム項目パラメータ
            $searchParam[$p] = Request::get($p);
        }

        // 「ユーザー管理」のカスタムカテゴリIDを取得
        $customCategoryCode = config('consts.user_custom_categories.CUSTOM_CATEGORY_USER');

        $formSelects = [
            'statuses' => ['' => 'すべて'] + get_const_item('staffs', 'status'),
            'searchParam' => $searchParam,
            'agencyRoles' => ['' => 'すべて'] + $this->agencyRoleService->getNamesByAgencyId($agencyId),
            // 所属カスタムデータ
            'shozokuItemData' => $userCustomItemDatas->firstWhere('code', config('consts.user_custom_items.CODE_STAFF_SHOZOKU')),
            // 「所属」以外のカスタムデータ
            'userCustomItemDatas' => $userCustomItemDatas->whereNotIn('code', [config('consts.user_custom_items.CODE_STAFF_SHOZOKU')])
        ];

        $view->with(compact('formSelects', 'searchParam', 'agencyId', 'customCategoryCode'));
    }
}
