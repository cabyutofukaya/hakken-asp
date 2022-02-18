<?php
namespace App\Http\ViewComposers\Staff\User;

use App\Models\AgencyConsultation;
use App\Models\Reserve;
use App\Services\ConsultationService;
use App\Services\CountryService;
use App\Services\StaffService;
use App\Services\UserCustomCategoryService;
use App\Services\UserCustomItemService;
use App\Services\UserService;
use App\Traits\JsConstsTrait;
use Auth;
use Illuminate\Support\Arr;
use Illuminate\View\View;

/**
 * 表示ページに使う選択項目などを提供するViewComposer
 */
class ShowFormComposer
{
    use JsConstsTrait;

    public function __construct(
        CountryService $countryService,
        UserCustomCategoryService $userCustomCategoryService,
        UserCustomItemService $userCustomItemService,
        UserService $userService,
        StaffService $staffService,
        ConsultationService $consultationService
    ) {
        $this->countryService = $countryService;
        $this->userCustomCategoryService = $userCustomCategoryService;
        $this->userCustomItemService = $userCustomItemService;
        $this->userService = $userService;
        $this->staffService = $staffService;
        $this->consultationService = $consultationService;
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
        $agencyAccount = $my->agency->account;

        $defaultTab = request()->input('tab', config('consts.users.DEFAULT_TAB'));

        $customCategoryCode = config('consts.user_custom_categories.CUSTOM_CATEGORY_PERSON'); // 個人顧客カスタム項目Code

        // タブコード一覧
        $tabCodes = config('consts.users.TAB_LIST');

        // 初期入力値。タブ毎に値をセット
        $defaultValue = [
            config('consts.users.TAB_CONSULTATION') => [
                'user_id' => $user->id, // 個人顧客ID
                'manager_id' => $my->id, // 自社担当者初期値。自分自身のID
                'status' => config('consts.agency_consultations.DEFAULT_STATUS'), // 状態
                'kind' => config('consts.agency_consultations.DEFAULT_KIND'), // 種別
            ]
        ];

        ///// 個人顧客カスタム項目 ////

        // 個人顧客に設定されたカスタム項目を取得
        $userCustomItems = $this->userCustomItemService->getByCategoryCodeForAgencyAccount(
            $customCategoryCode,
            $agencyAccount,
            true,
            [],
            // [
            //     'user_custom_items.key',
            //     'user_custom_items.name',
            //     'user_custom_items.code',
            //     'user_custom_items.display_position',
            //     'user_custom_items.unedit_item',
            // ]
        );
        
        // usersレコードに設定されたカスタム項目値
        $vUserCustomValues = $user->v_user_custom_values;
        // カスタム項目値とval値をセット
        $customValues = [];
        foreach ($userCustomItems->filter(function ($row, $key) {
            return $row['code'] !== config('consts.user_custom_items.CODE_USER_CUSTOMER_AIRPLANE_COMPANY');
        }) as $uci) { // マイレージモーダルのカスタム項目以外を取得（ユーザーの基本情報のみ）
            $tmp = $uci->only(['key','name','display_position','code']);
            $valueRow = $vUserCustomValues->firstWhere('key', $uci->key);
            $tmp['val'] = $valueRow ? $valueRow->val : null;
            $customValues[] = $tmp;
        }

        ///// 相談履歴カスタム項目 ////

        // 相談履歴に設定されたカスタム項目を取得
        $consultationUserCustomItems = $this->userCustomItemService->getByCategoryCodeForAgencyAccount(
            config('consts.user_custom_categories.CUSTOM_CATEGORY_CONSULTATION'),
            $agencyAccount,
            true,
            [],
            // [
            //     'user_custom_items.key',
            //     'user_custom_items.type',
            //     'user_custom_items.code',
            //     'user_custom_items.list',
            //     'user_custom_items.name',
            //     'user_custom_items.display_position',
            //     'user_custom_items.unedit_item',
            // ]
        );


        ////////////////////////////////

        // form値。タブ毎に値をセット
        $formSelects = [
            // 顧客
            config('consts.users.TAB_CUSTOMER_INFO') =>
            [
                'sexes' => get_const_item('users', 'sex'), // 性別
                'ageKbns' => get_const_item('users', 'age_kbn'), // 年齢区分
                'countries' => ['' => '-'] + $this->countryService->getCodeNameList(), // 国名リスト
                'dms' => ['' => '-'] + get_const_item('users', 'dm'), // DM可否
                'userCustomItems' => [
                    config('consts.user_custom_items.POSITION_PERSON_MILEAGE_MODAL') => $userCustomItems->where('display_position', config('consts.user_custom_items.POSITION_PERSON_MILEAGE_MODAL')),// ユーザーマイレージのモーダル用。カスタム項目値ではなく"selectフォームを作成するための設定値"としてセット
                ]
            ],
            // 履歴
            config('consts.users.TAB_USAGE_HISTORY') =>
            [
            ],
            // 相談
            config('consts.users.TAB_CONSULTATION') =>
            [
                'staffs' => ['' => '-'] + $this->staffService->getIdNameSelect($agencyAccount, true), // 自社スタッフ
                'statuses' => ['' => '-'] + get_const_item('agency_consultations', 'status'), // スタータス
                'kinds' => get_const_item('agency_consultations', 'kind'), // 種別
                'userCustomItems' => [
                    config('consts.user_custom_items.POSITION_CONSULTATION_CUSTOM_FIELD') => $consultationUserCustomItems->where('display_position', config('consts.user_custom_items.POSITION_CONSULTATION_CUSTOM_FIELD')),// 相談モーダル用。カスタム項目値ではなく"selectフォームを作成するための設定値"としてセット
                ]

            ],
        ];

        // カスタム項目”値”。タブ、表示位置毎に値をセット
        $customFields = [
            config('consts.users.TAB_CUSTOMER_INFO') => [
                config('consts.user_custom_items.POSITION_PERSON_CUSTOM_FIELD') =>
                    collect($customValues)->where('display_position', config('consts.user_custom_items.POSITION_PERSON_CUSTOM_FIELD'))->toArray(),
                config('consts.user_custom_items.POSITION_PERSON_WORKSPACE_SCHOOL') =>
                    collect($customValues)->where('display_position', config('consts.user_custom_items.POSITION_PERSON_WORKSPACE_SCHOOL'))->toArray(),
                config('consts.user_custom_items.POSITION_PERSON_EMERGENCY_CONTACT') =>
                    collect($customValues)->where('display_position', config('consts.user_custom_items.POSITION_PERSON_EMERGENCY_CONTACT'))->toArray(),
            ],
            // 相談一覧 > 相談モーダル
            config('consts.users.TAB_CONSULTATION') => [
            ],
        ];

        // 各種定数値。タブ毎にセット
        $consts = [
            // 顧客
            config('consts.users.TAB_CUSTOMER_INFO') => [
                // カスタムフィールド表示位置
                'customFieldPositions' => [
                    'position_person_custom_field' => config('consts.user_custom_items.POSITION_PERSON_CUSTOM_FIELD'),
                    'position_person_workspace_school' => config('consts.user_custom_items.POSITION_PERSON_WORKSPACE_SCHOOL'),
                    'position_person_emergency_contact' => config('consts.user_custom_items.POSITION_PERSON_EMERGENCY_CONTACT'),
                    'position_person_mileage_modal' => config('consts.user_custom_items.POSITION_PERSON_MILEAGE_MODAL'),
                ],
                // カスタム項目管理コード
                'customFieldCodes' => [
                    'code_user_customer_kbn' => config('consts.user_custom_items.CODE_USER_CUSTOMER_KBN'),
                    'code_user_customer_rank' => config('consts.user_custom_items.CODE_USER_CUSTOMER_RANK'),
                    'code_user_customer_receptionist' => config('consts.user_custom_items.CODE_USER_CUSTOMER_RECEPTIONIST'),
                    'code_user_customer_airplane_company' => config('consts.user_custom_items.CODE_USER_CUSTOMER_AIRPLANE_COMPANY'),
                ],
                // 航空会社のカスタム項目キー
                'codeUserCustomerAirplaneCompanyKey' => $this->userCustomItemService->getKeyByCodeForAgency(config('consts.user_custom_items.CODE_USER_CUSTOMER_AIRPLANE_COMPANY'), $my->agency_id),
            ],
            //　履歴
            config('consts.users.TAB_USAGE_HISTORY') =>[
                'application_step_draft' => config('consts.reserves.APPLICATION_STEP_DRAFT'), // 見積
                'application_step_reserve' => config('consts.reserves.APPLICATION_STEP_RESERVE'), // 予約
                'estimateNormalCreateUrl' => route('staff.asp.estimates.normal.create', [$agencyAccount]),
            ],
            // 相談
            config('consts.users.TAB_CONSULTATION') =>
            [
                // ステータス値
                'statusList' => [
                    'status_reception' => config('consts.agency_consultations.STATUS_RECEPTION'),
                    'status_responding' => config('consts.agency_consultations.STATUS_RESPONDING'),
                    'status_completion' => config('consts.agency_consultations.STATUS_COMPLETION'),
                ],
            ]
        ];

        // 認可情報
        $permission = [
            // 個人顧客
            config('consts.users.TAB_CUSTOMER_INFO') => [
                'read' => Auth::user('staff')->can('view', $user), // 閲覧権限
                'update' => Auth::user('staff')->can('update', $user), // 更新権限
                'delete' => Auth::user('staff')->can('delete', $user), // 削除権限
            ],
            // 利用履歴
            config('consts.users.TAB_USAGE_HISTORY') => [
                'create' => Auth::user('staff')->can('create', Reserve::class), // 見積作成権限
                'read' => Auth::user('staff')->can('viewAny', $user), // 閲覧権限
            ],
            // 個人顧客相談
            config('consts.users.TAB_CONSULTATION') => [
                'create' => Auth::user('staff')->can('create', AgencyConsultation::class), // 作成権限
                'read' => Auth::user('staff')->can('viewAny', AgencyConsultation::class), // 閲覧権限
            ]
        ];


        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agencyAccount);

        $view->with(compact('defaultTab', 'tabCodes', 'defaultValue', 'formSelects', 'permission', 'customFields', 'consts', 'customCategoryCode', 'jsVars'));
    }
}
