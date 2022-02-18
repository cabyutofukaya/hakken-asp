<?php
namespace App\Http\ViewComposers\Staff\ReserveEstimate;

use App\Services\BusinessUserService;
use App\Services\CountryService;
use App\Services\MasterAreaService;
use App\Services\PrefectureService;
use App\Services\ReserveService;
use App\Services\StaffService;
use App\Services\UserCustomCategoryService;
use App\Services\UserCustomItemService;
use App\Services\UserService;
use App\Services\VAreaService;
use App\Traits\ConstsTrait;
use App\Traits\UserCustomItemTrait;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use App\Traits\JsConstsTrait;

/**
 * 作成ページで使う選択項目などを提供するViewComposer
 * 見積・予約共通
 */
class CreateFormComposer
{
    use UserCustomItemTrait, ConstsTrait, JsConstsTrait;

    public function __construct(
        CountryService $countryService,
        MasterAreaService $masterAreaService,
        PrefectureService $prefectureService,
        ReserveService $reserveService,
        StaffService $staffService,
        UserCustomCategoryService $userCustomCategoryService,
        UserCustomItemService $userCustomItemService,
        UserService $userService,
        VAreaService $vAreaService,
        BusinessUserService $businessUserService
    ) {
        $this->countryService = $countryService;
        $this->masterAreaService = $masterAreaService;
        $this->prefectureService = $prefectureService;
        $this->reserveService = $reserveService;
        $this->staffService = $staffService;
        $this->userCustomCategoryService = $userCustomCategoryService;
        $this->userCustomItemService = $userCustomItemService;
        $this->userService = $userService;
        $this->vAreaService = $vAreaService;
        $this->businessUserService = $businessUserService;
    }

    /**
     * @param View $view
     * @return void
     */
    public function compose(View $view)
    {
        $data = $view->getData(); // controllerにセットされたデータを取得
        $applicationStep = Arr::get($data, 'applicationStep'); // 申込段階

        //////////////////////////////////

        $my = auth("staff")->user();
        $agencyAccount = $my->agency->account;


        $customCategoryCode = config('consts.user_custom_categories.CUSTOM_CATEGORY_RESERVE');

        $defaultValue = session()->getOldInput();

        if (!Arr::get($defaultValue, "manager_id")) {
            $defaultValue['manager_id'] = $my->id; // POSTされた値が無ければ自社担当者を自分自身で初期化
        }

        /**
         * 申込者情報
         * POSTされた値が無ければ空で初期化
         * ユーザー指定のGETパラメータがある場合は、申込者種別・ユーザー検索番号・選択番号を然るべき値で初期化
         */
        if (!Arr::get($defaultValue, "participant_type")) {
            $defaultValue['participant_type'] = ""; // 申し込み者検索の「個人/法人」種別
            $defaultValue['search_user_number'] = ""; // ユーザー番号の検索値
            $defaultValue['applicant_user_number'] = ""; // ユーザー番号の選択値


            // 申込者情報がGETパラメータで渡ってきた場合は情報をセット
            if ($userNumber = request()->input('user_number')) {
                $user = $this->userService->findByUserNumber($userNumber, $agencyAccount);
                $defaultValue['participant_type'] = config('consts.reserves.PARTICIPANT_TYPE_PERSON');
                $defaultValue['search_user_number'] = $userNumber;
                $defaultValue['applicant_user_number'] = $userNumber;
            } elseif ($businessUserNumber = request()->input('business_user_number')) {
                // 法人顧客の場合は担当者まで特定できないので、applicant_user_numberの設定はナシ
                $businessUser = $this->businessUserService->findByUserNumber($businessUserNumber, $agencyAccount);
                $defaultValue['participant_type'] = config('consts.reserves.PARTICIPANT_TYPE_BUSINESS');
                $defaultValue['search_user_number'] = $businessUserNumber;
            } else {
                $defaultValue['participant_type'] = config('consts.reserves.PARTICIPANT_TYPE_DEFAULT');
            }
        }
        $defaultValue['applicant_search_get_deleted'] = false; // 申込者検索の際に論理削除を取得するか否か


        if (Arr::get($defaultValue, "departure_id")) { // 出発地IDがある場合は初期値用に名称等も取得
            $defaultValue['departure'] = $this->vAreaService->getDefaultSelectRow($defaultValue['departure_id']);
        }
        if (Arr::get($defaultValue, "destination_id")) { // 目的地IDがある場合は初期値用に名称等も取得
            $defaultValue['destination'] = $this->vAreaService->getDefaultSelectRow($defaultValue['destination_id']);
        }

        // ユーザー追加モーダルのデフォルト値
        $userAddModalDefaultValue = [
            'userable' => [
                'sex' => config('consts.participants.DEFAULT_SEX'),
                'passport_issue_country_code' => config('consts.participants.DEFAULT_PASSPORT_ISSUE_COUNTRY'),
                'citizenship_code' => config('consts.participants.DEFAULT_CITIZENSHIP'),
                'user_ext' => [
                    'age_kbn' => config('consts.participants.DEFAULT_AGE_KBN'),
                ]
            ]
        ];

        // カスタム項目のinput初期値をセット
        $userCustomItems = $this->getUserCustomItemsAndSetCustomFieldDefaultCreateInput(
            $defaultValue,
            $this->userCustomItemService,
            $agencyAccount,
            config('consts.user_custom_categories.CUSTOM_CATEGORY_RESERVE')
        );
        if ($applicationStep === config('consts.reserves.APPLICATION_STEP_DRAFT')) { // 見積。userCustomItemsから予約ステータス項目除去。予約/見積項目のみイレギュラーにつき
            $userCustomItems = $userCustomItems->filter(function ($row, $key) {
                return $row['code'] !== config('consts.user_custom_items.CODE_APPLICATION_RESERVE_STATUS');
            });
            // 見積の初期値を設定
            $st = $userCustomItems->firstWhere('code', config('consts.user_custom_items.CODE_APPLICATION_ESTIMATE_STATUS'));
            if (!$defaultValue[$st->key]) {
                $defaultValue[$st->key] = config('consts.reserves.ESTIMATE_DEFAULT_STATUS'); // 見積
            }
        } elseif ($applicationStep === config('consts.reserves.APPLICATION_STEP_RESERVE')) { // 予約。userCustomItemsから見積ステータス項目除去。予約/見積項目のみイレギュラーにつき
            $userCustomItems = $userCustomItems->filter(function ($row, $key) {
                return $row['code'] !== config('consts.user_custom_items.CODE_APPLICATION_ESTIMATE_STATUS');
            });
            // 予約の初期値を設定
            $st = $userCustomItems->firstWhere('code', config('consts.user_custom_items.CODE_APPLICATION_RESERVE_STATUS'));
            if (!$defaultValue[$st->key]) {
                $defaultValue[$st->key] = config('consts.reserves.RESERVE_DEFAULT_STATUS'); // 手配中
            }
        }
        // 申込日がPOSTされていない場合は本日付けで初期化
        $appDate = $userCustomItems->firstWhere('code', config('consts.user_custom_items.CODE_APPLICATION_APPLICATION_DATE'));
        if (!$defaultValue[$appDate->key]) {
            $defaultValue[$appDate->key] = date('Y/m/d');
        }

        $formSelects = [
            'staffs' => ['' => '---'] + $this->staffService->getIdNameSelect($agencyAccount, false), // 自社スタッフ。削除済み除く
            'participantTypes' => get_const_item('reserves', 'participant_type'),
            'defaultAreas' => $this->masterAreaService->getDefaultOptions(['label' => '---', 'value' => '']),
            'countries' => ['' => '-'] + $this->countryService->getCodeNameList(), // 国名リスト
            'sexes' => get_const_item('users', 'sex'), // 性別
            'ageKbns' => ['' => '-'] + get_const_item('users', 'age_kbn'), // 年齢区分
            'birthdayYears' => ['' => '年'] + $this->userService->getBirthDayYearSelect(), // 誕生日年（「YYYY => YYYY年」形式の配列）
            'birthdayMonths' => ['' => '月'] + $this->userService->getBirthDayMonthSelect(), // 誕生日月（「MM => MM月」形式の配列）
            'birthdayDays' => ['' => '日'] + $this->userService->getBirthDayDaySelect(), // 誕生日日（「DD => DD月」形式の配列）
            'prefectures' => ['' => '都道府県'] + $this->prefectureService->getCodeNameList(), // 都道府県（「都道府県コード => 都道府県名」形式の配列）,
        ];

        $consts = [
            // 個人・法人種別
            'customerKbns' => [
                'person' => config('consts.reserves.PARTICIPANT_TYPE_PERSON'),
                'business' => config('consts.reserves.PARTICIPANT_TYPE_BUSINESS')
            ],
            // カスタムフィールド表示位置
            'customFieldPositions' => [
                'base' => config('consts.user_custom_items.POSITION_APPLICATION_BASE_FIELD'),
                'custom' => config('consts.user_custom_items.POSITION_APPLICATION_CUSTOM_FIELD')
            ],
            // カスタム項目管理コード
            'customFieldCodes' => [
                'travel_type' => config('consts.user_custom_items.CODE_APPLICATION_TRAVEL_TYPE')
            ],
            // カスタム項目タイプ
            'customFieldTypes' => $this->getCustomFieldTypes(),
            // カスタム項目入力タイプ
            'customFieldInputTypes' => $this->getCustomFieldInputTypes(),
        ];

        // カスタム項目。表示位置毎に値をセット
        $customFields = [
            config('consts.user_custom_items.POSITION_APPLICATION_BASE_FIELD') => $userCustomItems->where('display_position', config('consts.user_custom_items.POSITION_APPLICATION_BASE_FIELD')), // 基本情報
            config('consts.user_custom_items.POSITION_APPLICATION_CUSTOM_FIELD') => $userCustomItems->where('display_position', config('consts.user_custom_items.POSITION_APPLICATION_CUSTOM_FIELD')), // カスタムフィールド
        ];

        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agencyAccount);

        $view->with(compact('defaultValue', 'formSelects', 'consts', 'customCategoryCode', 'customFields', 'userAddModalDefaultValue', 'jsVars'));
    }
}
