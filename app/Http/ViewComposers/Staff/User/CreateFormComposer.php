<?php
namespace App\Http\ViewComposers\Staff\User;

use App\Services\CountryService;
use App\Services\PrefectureService;
use App\Services\StaffService;
use App\Services\UserCustomCategoryService;
use App\Services\UserCustomItemService;
use App\Services\UserService;
use App\Traits\JsConstsTrait;
use App\Traits\UserCustomItemTrait;
use Illuminate\Support\Arr;
use Illuminate\View\View;

/**
 * 作成フォームに使う選択項目などを提供するViewComposer
 */
class CreateFormComposer
{
    use UserCustomItemTrait, JsConstsTrait;

    public function __construct(
        CountryService $countryService,
        PrefectureService $prefectureService,
        StaffService $staffService,
        UserCustomCategoryService $userCustomCategoryService,
        UserCustomItemService $userCustomItemService,
        UserService $userService
    ) {
        $this->countryService = $countryService;
        $this->prefectureService = $prefectureService;
        $this->staffService = $staffService;
        $this->userCustomCategoryService = $userCustomCategoryService;
        $this->userCustomItemService = $userCustomItemService;
        $this->userService = $userService;
    }

    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        $my = auth("staff")->user();
        $agencyAccount = $my->agency->account;

        $customCategoryCode = config('consts.user_custom_categories.CUSTOM_CATEGORY_PERSON');

        $defaultValue = session()->getOldInput();
        if (!Arr::get($defaultValue, "userable.user_ext.manager_id")) {
            $defaultValue['userable']['user_ext']['manager_id'] = $my->id; // POST値が無ければ自社担当者を自身のIDで初期化
        }

        // カスタム項目のinput初期値をセット
        $userCustomItems = $this->getUserCustomItemsAndSetCustomFieldDefaultCreateInput(
            $defaultValue,
            $this->userCustomItemService,
            $agencyAccount,
            config('consts.user_custom_categories.CUSTOM_CATEGORY_PERSON')
        );

        $formSelects = [
            'staffs' => ['' => '-'] + $this->staffService->getIdNameSelect($agencyAccount, false), // 自社スタッフ。削除済みを除く
            'sexes' => get_const_item('users', 'sex'), // 性別
            'ageKbns' => ['' => '-'] + get_const_item('users', 'age_kbn'), // 年齢区分
            'birthdayYears' => ['' => '年'] + $this->userService->getBirthDayYearSelect(), // 誕生日年（「YYYY => YYYY年」形式の配列）
            'birthdayMonths' => ['' => '月'] + $this->userService->getBirthDayMonthSelect(), // 誕生日月（「MM => MM月」形式の配列）
            'birthdayDays' => ['' => '日'] + $this->userService->getBirthDayDaySelect(), // 誕生日日（「DD => DD月」形式の配列）
            'prefectures' => ['' => '都道府県'] + $this->prefectureService->getCodeNameList(), // 都道府県（「都道府県コード => 都道府県名」形式の配列）,
            'countries' => ['' => '-'] + $this->countryService->getCodeNameList(), // 国名リスト
            'dms' => ['' => '-'] + get_const_item('users', 'dm'), // DM可否
            'userCustomItems' => [
                config('consts.user_custom_items.POSITION_PERSON_CUSTOM_FIELD') => $userCustomItems->where('display_position', config('consts.user_custom_items.POSITION_PERSON_CUSTOM_FIELD')),
                config('consts.user_custom_items.POSITION_PERSON_WORKSPACE_SCHOOL') => $userCustomItems->where('display_position', config('consts.user_custom_items.POSITION_PERSON_WORKSPACE_SCHOOL')),
                config('consts.user_custom_items.POSITION_PERSON_EMERGENCY_CONTACT') => $userCustomItems->where('display_position', config('consts.user_custom_items.POSITION_PERSON_EMERGENCY_CONTACT')),
                config('consts.user_custom_items.POSITION_PERSON_MILEAGE_MODAL') => $userCustomItems->where('display_position', config('consts.user_custom_items.POSITION_PERSON_MILEAGE_MODAL')),
            ], // カスタムフィールド情報を”表示位置”毎に取得
        ];
        
        $consts = [
            // 航空会社のカスタム項目キー
            'codeUserCustomerAirplaneCompanyKey' => $this->userCustomItemService->getKeyByCodeForAgency(config('consts.user_custom_items.CODE_USER_CUSTOMER_AIRPLANE_COMPANY'), $my->agency_id),
        ];

        ////// reactに渡す値 ///////

        // 基本情報等
        $userableDefaultValue = collect($defaultValue)->only(['userable']);

        $addressFormSelects = collect($formSelects)->only(['prefectures']);

        // ビザ情報
        $visaDefaultValue = collect($defaultValue)->only(['user_visas']);
        $visaFormSelects = collect($formSelects)->only(['countries']);
        $visaUserCustomItems = [];

        // マイレージ
        $mileageDefaultValue = collect($defaultValue)->only(['user_mileages']);
        $mileageFormSelects = [];
        $mileageUserCustomItems = $formSelects['userCustomItems'][config('consts.user_custom_items.POSITION_PERSON_MILEAGE_MODAL')]; // カスタム項目

        // メンバーズカード
        $memberCardDefaultValue = collect($defaultValue)->only(['user_member_cards']);
        $memberCardUserCustomItems = [];
        

        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agencyAccount);

        $view->with(compact('defaultValue', 'formSelects', 'userableDefaultValue', 'addressFormSelects', 'visaFormSelects', 'visaUserCustomItems', 'customCategoryCode', 'visaDefaultValue', 'mileageDefaultValue', 'mileageFormSelects', 'mileageUserCustomItems', 'memberCardDefaultValue', 'memberCardUserCustomItems', 'consts', 'jsVars'));
    }
}
