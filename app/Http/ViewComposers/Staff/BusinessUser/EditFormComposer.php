<?php
namespace App\Http\ViewComposers\Staff\BusinessUser;

use Hashids;
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
 * 編集フォームに使う選択項目などを提供するViewComposer
 */
class EditFormComposer
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
        $data = $view->getData(); // controllerにセットされたデータを取得
        $businessUser = Arr::get($data, 'businessUser');

        //////////////////////////////////

        $my = auth("staff")->user();
        $agencyId = $my->agency->id;
        $agencyAccount = $my->agency->account;

        $customCategoryCode = config('consts.user_custom_categories.CUSTOM_CATEGORY_BUSINESS');

        // 当該マスタに設定されたカスタム項目を取得
        $userCustomItems = $this->userCustomItemService->getByCategoryCodeForAgencyAccount(
            config('consts.user_custom_categories.CUSTOM_CATEGORY_BUSINESS'),
            $agencyAccount, 
            true
        );

        //////////////// form初期値を設定 ////////////////
        // 基本項目
        foreach ([
            'name_kana',
            'name_roman',
            'tel',
            'fax',
            'zip_code',
            'prefecture_code',
            'address1',
            'address2',
            'manager_id',
            'pay_altogether',
            'note',
            'updated_at',
            'business_user_managers',
            ] as $f) {
                $defaultValue[$f] = old($f, data_get($businessUser, $f));
        }
        // 名前はオリジナルのものに書き換えておく
        $defaultValue['name'] = old('name', data_get($businessUser, 'org_name'));


        // 当該レコードに設定されたカスタム項目値
        $vBusinessUserCustomValues = $businessUser->v_business_user_custom_values;
        foreach ($userCustomItems->pluck('key') as $key) {
            $row = $vBusinessUserCustomValues->firstWhere('key', $key);
            $defaultValue[$key] = old($key, Arr::get($row, 'val')); // val値をセット
        }
        ////////////////////////////////
        

        $formSelects = [
            'staffs' => ['' => '-'] + $this->staffService->getIdNameSelectSafeValues($agencyId, [$businessUser->manager_id]), // 自社スタッフ
            'pay_altogethers' => get_const_item('business_users', 'pay_altogether'), // 一括支払契約
            'userCustomItems' => [
                config('consts.user_custom_items.POSITION_BUSINESS_CUSTOM_FIELD') => $userCustomItems->where('display_position', config('consts.user_custom_items.POSITION_BUSINESS_CUSTOM_FIELD')),
            ], // カスタムフィールド情報を”表示位置”毎に取得
        ];

        ////// reactに渡す値 ///////

        // 住所入力欄
        $addressDefaultValue = collect($defaultValue)->only(['zip_code','prefecture_code','address1','address2']); // bladeでonlyメソッドを呼び出すとなぜかシンタックスエラーになってしまうのでcomposer内で変数に抽出
        $addressFormSelects['prefectures'] = ['' => '都道府県'] + $this->prefectureService->getCodeNameList(); // 都道府県（「都道府県コード => 都道府県名」形式の配列

        // 担当者情報
        $managerDefaultValue = collect($defaultValue)->only(['business_user_managers']);
        $managerFormSelects['dms'] = ['' => '-'] + get_const_item('business_user_managers', 'dm'); // DM可否

        $managerFormSelects['sexes'] = get_const_item('users', 'sex'); // 性別
        
        $view->with(compact('defaultValue', 'formSelects', 'customCategoryCode', 'managerDefaultValue', 'managerFormSelects', 'addressDefaultValue', 'addressFormSelects'));
    }
}
