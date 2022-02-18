<?php
namespace App\Http\ViewComposers\Staff\User;

use App\Services\CountryService;
use App\Services\PrefectureService;
use App\Services\StaffService;
use App\Services\UserCustomCategoryService;
use App\Services\UserCustomItemService;
use App\Services\UserService;
use App\Traits\UserCustomItemTrait;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use App\Traits\JsConstsTrait;

/**
 * 編集フォームに使う選択項目などを提供するViewComposer
 */
class EditFormComposer
{
    use UserCustomItemTrait, JsConstsTrait;

    public function __construct(
        CountryService $countryService,
        PrefectureService $prefectureService,
        UserCustomCategoryService $userCustomCategoryService,
        UserCustomItemService $userCustomItemService,
        UserService $userService,
        StaffService $staffService
    ) {
        $this->countryService = $countryService;
        $this->prefectureService = $prefectureService;
        $this->userCustomCategoryService = $userCustomCategoryService;
        $this->userCustomItemService = $userCustomItemService;
        $this->userService = $userService;
        $this->staffService = $staffService;
    }

    /**
     * @param View $view
     * @return void
     */

    public function compose(View $view)
    {
        $data = $view->getData(); // controllerにセットされたデータを取得
        $user = Arr::get($data, 'user');

        //////////////////////////////////

        $my = auth("staff")->user();
        $agencyId = $my->agency->id;
        $agencyAccount = $my->agency->account;

        $customCategoryCode = config('consts.user_custom_categories.CUSTOM_CATEGORY_PERSON');

        // 当該マスタに設定されたカスタム項目を取得
        $userCustomItems = $this->userCustomItemService->getByCategoryCodeForAgencyAccount(
            config('consts.user_custom_categories.CUSTOM_CATEGORY_PERSON'),
            $agencyAccount,
            true
        );

        //////////////// form初期値を設定 ////////////////
        $defaultValue = array_merge($user->toArray(), session()->getOldInput());

        // 当該レコードに設定されたカスタム項目値
        $vUserCustomValues = $user->v_user_custom_values;
        foreach ($userCustomItems->filter(function ($row) {
            return $row->display_position !== config('consts.user_custom_items.POSITION_PERSON_MILEAGE_MODAL'); // マイレージモーダルのカスタム項目以外を取得
        })->pluck('key') as $key) {
            $row = $vUserCustomValues->firstWhere('key', $key);
            $defaultValue[$key] = old($key, Arr::get($row, 'val')); // val値をセット
        }

        // user_mileagesレコードに設定されたカスタム項目値をセット。
        // メンバーズカードとVISAでカスタム項目を設置する場合は以下と同じ手順の処理を追加すればOK
        foreach ($defaultValue['user_mileages'] as $k => $um) {
            foreach (Arr::get($um, 'v_user_mileage_custom_values', []) as $umcv) {
                $defaultValue['user_mileages'][$k][$umcv['key']] = $umcv['val'];
            }
            unset($defaultValue['user_mileages'][$k]['v_user_mileage_custom_values']); //配列にv_user_mileage_custom_valuesプロパティが残ってしまうので一応削除
        }

        ////////////////////////////////

        $formSelects = [
            'staffs' => ['' => '-'] + $this->staffService->getIdNameSelectSafeValues($agencyId, [optional($user->userable->user_ext)->manager_id]), // 自社スタッフ
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
            // カスタム項目管理コード
            'customFieldCodes' => [
                'code_user_customer_kbn' => config('consts.user_custom_items.CODE_USER_CUSTOMER_KBN'),
                'code_user_customer_rank' => config('consts.user_custom_items.CODE_USER_CUSTOMER_RANK'),
                'code_user_customer_receptionist' => config('consts.user_custom_items.CODE_USER_CUSTOMER_RECEPTIONIST'),
                'code_user_customer_airplane_company' => config('consts.user_custom_items.CODE_USER_CUSTOMER_AIRPLANE_COMPANY'),
            ],
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
