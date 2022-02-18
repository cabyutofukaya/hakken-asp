<?php
namespace App\Http\ViewComposers\Staff\Staff;

use Illuminate\Support\Arr;
use Illuminate\View\View;
use Lang;
use App\Services\StaffService;
use App\Services\UserCustomItemService;

/**
 * スタッフ編集フォームに使う選択項目などを提供するViewComposer
 */
class EditFormComposer
{
    public function __construct(StaffService $staffService, UserCustomItemService $userCustomItemService)
    {
        $this->staffService = $staffService;
        $this->userCustomItemService = $userCustomItemService;
    }
    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        $data = $view->getData(); // controllerにセットされたデータを取得
        $staff = Arr::get($data, 'staff');

        //////////////////////////////////
        
        $staffAccount = $staff->account;

        // スタッフ情報のカスタムカテゴリ識別コード
        $customCategoryCode = config('consts.user_custom_categories.CUSTOM_CATEGORY_USER');

        $customItemKeys= $this->userCustomItemService->getByCategoryCodeForAgencyAccount(
            $customCategoryCode, 
            $staff->agency->account, 
            true
            )->pluck('key'); // スタッフ情報のカスタムカテゴリの項目キー一覧を取得
                
        //////////////// form初期値を設定 ////////////////
        // 基本項目
        $defaultValue = [
            'name'              => old('name', $staff->org_name),
            'account'           => old('account', $staffAccount),
            'email'             => old('email', $staff->email),
            'agency_role_id'    => old('agency_role_id', $staff->agency_role_id),
            'status'            => old('status', $staff->status)

        ];

        // 当該レコードに設定されたカスタム項目値
        $vStaffCustomValues = $staff->v_staff_custom_values;
        foreach ($customItemKeys as $key) {
            $row = $vStaffCustomValues->firstWhere('key', $key);
            $defaultValue[$key] = old($key, Arr::get($row, 'val')); // val値をセット
        }
        ////////////////////////////////

        $formSelects = [
            'statuses' => get_const_item('staffs', 'status'), // 状態ステータス
        ];

        $userCustomItemTypes = config('consts.user_custom_items.CUSTOM_ITEM_LIST');
        
        $view->with(compact('staffAccount', 'formSelects', 'defaultValue', 'customCategoryCode', 'userCustomItemTypes'));
    }
}
