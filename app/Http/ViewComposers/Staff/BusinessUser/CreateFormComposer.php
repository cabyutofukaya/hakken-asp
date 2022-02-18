<?php
namespace App\Http\ViewComposers\Staff\BusinessUser;

use App\Services\BusinessUserManagerService;
use App\Services\BusinessUserService;
use App\Services\PrefectureService;
use App\Services\StaffService;
use App\Services\UserCustomCategoryService;
use App\Services\UserCustomItemService;
use App\Traits\UserCustomItemTrait;
use Illuminate\Support\Arr;
use Illuminate\View\View;

/**
 * 作成フォームに使う選択項目などを提供するViewComposer
 */
class CreateFormComposer
{
    use UserCustomItemTrait;

    public function __construct(
        BusinessUserManagerService $businessUserManagerService,
        BusinessUserService $businessUserService,
        PrefectureService $prefectureService,
        StaffService $staffService,
        UserCustomCategoryService $userCustomCategoryService,
        UserCustomItemService $userCustomItemService
    ) {
        $this->businessUserManagerService = $businessUserManagerService;
        $this->businessUserService = $businessUserService;
        $this->prefectureService = $prefectureService;
        $this->staffService = $staffService;
        $this->userCustomCategoryService = $userCustomCategoryService;
        $this->userCustomItemService = $userCustomItemService;
    }

    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        $my = auth("staff")->user();
        $agencyAccount = $my->agency->account;

        $customCategoryCode = config('consts.user_custom_categories.CUSTOM_CATEGORY_BUSINESS');

        $defaultValue = session()->getOldInput();
        if (!Arr::get($defaultValue, "manager_id")) {
            $defaultValue['manager_id'] = $my->id; // POSTされた値が無ければ自社担当者を自分自身のIDで初期化
        }

        // カスタム項目のinput初期値をセット
        $userCustomItems = $this->getUserCustomItemsAndSetCustomFieldDefaultCreateInput(
            $defaultValue,
            $this->userCustomItemService,
            $agencyAccount,
            config('consts.user_custom_categories.CUSTOM_CATEGORY_BUSINESS')
        );

        $formSelects = [
            'pay_altogethers' => get_const_item('business_users', 'pay_altogether'), // 一括支払契約
            'staffs' => ['' => '-'] + $this->staffService->getIdNameSelect($agencyAccount, false), // 自社スタッフ
            'userCustomItems' => [
                config('consts.user_custom_items.POSITION_BUSINESS_CUSTOM_FIELD') => $userCustomItems->where('display_position', config('consts.user_custom_items.POSITION_BUSINESS_CUSTOM_FIELD')),
            ], // カスタムフィールド情報を”表示位置”毎に取得
        ];

        ////// reactに渡す値 ///////

        // 住所入力欄
        $addressDefaultValue = collect($defaultValue)->only(['zip_code','prefecture_code','address1','address2']); // bladeでonlyメソッドを呼び出すとなぜかシンタックスエラーになってしまうのでcomposer内で変数に抽出
        $addressFormSelects['prefectures'] = ['' => '都道府県'] + $this->prefectureService->getCodeNameList(); // 都道府県（「都道府県コード => 都道府県名」形式の配列

        // 取引先担当者情報
        $managerDefaultValue = collect($defaultValue)->only(['business_user_managers']);
        $managerFormSelects['dms'] = ['' => '-'] + get_const_item('business_user_managers', 'dm'); // DM可否

        $managerFormSelects['sexes'] = get_const_item('users', 'sex'); // 性別

        $view->with(compact('defaultValue', 'formSelects', 'customCategoryCode', 'managerDefaultValue', 'managerFormSelects', 'addressDefaultValue', 'addressFormSelects'));
    }
}
