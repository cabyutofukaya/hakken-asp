<?php
namespace App\Http\ViewComposers\Staff\BusinessUser;

use App\Models\AgencyConsultation;
use App\Models\Reserve;
use App\Services\BusinessUserManagerService;
use App\Services\BusinessUserService;
use App\Services\ConsultationService;
use App\Services\CountryService;
use App\Services\StaffService;
use App\Services\UserCustomCategoryService;
use App\Services\UserCustomItemService;
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
        BusinessUserManagerService $businessUserManagerService,
        BusinessUserService $businessUserService,
        CountryService $countryService,
        UserCustomCategoryService $userCustomCategoryService,
        UserCustomItemService $userCustomItemService,
        StaffService $staffService,
        ConsultationService $consultationService
    ) {
        $this->businessUserManagerService = $businessUserManagerService;
        $this->businessUserService = $businessUserService;
        $this->countryService = $countryService;
        $this->userCustomCategoryService = $userCustomCategoryService;
        $this->userCustomItemService = $userCustomItemService;
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
        $businessUser = Arr::get($data, 'businessUser');

        //////////////////////////////////

        $my = auth("staff")->user();
        $agencyAccount = $my->agency->account;

        $defaultTab = request()->input('tab', config('consts.business_users.DEFAULT_TAB'));

        // タブコード一覧
        $tabCodes = config('consts.business_users.TAB_LIST');

        // 初期入力値。タブ毎に値をセット
        $defaultValue = [
            config('consts.business_users.TAB_CONSULTATION') => [
                'manager_id' => $my->id, // 自社担当者初期値。自分自身のID
                'status' => config('consts.agency_consultations.DEFAULT_STATUS'), // 状態
                'kind' => config('consts.agency_consultations.DEFAULT_KIND'), // 種別
            ]
        ];
        
        // 当該マスタに設定されたカスタム項目を取得
        $userCustomItems = $this->userCustomItemService->getByCategoryCodeForAgencyAccount(
            config('consts.user_custom_categories.CUSTOM_CATEGORY_BUSINESS'),
            $agencyAccount,
            true,
            [],
            [
                'user_custom_items.key',
                'user_custom_items.name',
                'user_custom_items.display_position',
                'user_custom_items.unedit_item',
            ]
        );

        // 法人顧客レコードに設定されたカスタム項目値
        $vBusinessUserCustomValues = $businessUser->v_business_user_custom_values;

        // カスタム項目値とval値をセット
        $customValues = [];
        foreach ($userCustomItems as $uci) {
            $tmp = $uci->only(['key','name','display_position']);
            $valueRow = $vBusinessUserCustomValues->firstWhere('key', $uci->key);
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
            // 法人顧客
            config('consts.business_users.TAB_CUSTOMER_INFO') =>
            [
                'statuses' => $this->businessUserService->getStatusSelect(), // ステータス値
                'dms' => ['' => '-'] + get_const_item('business_user_managers', 'dm'), // DM可否
                'sexes' => get_const_item('users', 'sex'), // 性別
                'oneTimePayments' => ['' => '-'] + get_const_item('business_users', 'pay_altogether'), // 一括支払契約
            ],
            // 履歴
            config('consts.business_users.TAB_USAGE_HISTORY') =>
            [
            ],
            // 相談
            config('consts.business_users.TAB_CONSULTATION') =>
            [
               'staffs' => ['' => '-'] + $this->staffService->getIdNameSelect($agencyAccount, true), // 自社スタッフ
                'statuses' => ['' => '-'] + get_const_item('agency_consultations', 'status'), // スタータス
                'kinds' => get_const_item('agency_consultations', 'kind'), // 種別
                'userCustomItems' => [
                    config('consts.user_custom_items.POSITION_CONSULTATION_CUSTOM_FIELD') => $consultationUserCustomItems->where('display_position', config('consts.user_custom_items.POSITION_CONSULTATION_CUSTOM_FIELD')),// 相談モーダル用。カスタム項目値ではなく"selectフォームを作成するための設定値"としてセット
                ]
            ],
        ];

        // カスタム項目。タブ、表示位置毎に値をセット
        $customFields = [
            config('consts.business_users.TAB_CUSTOMER_INFO') => [
                config('consts.user_custom_items.POSITION_BUSINESS_CUSTOM_FIELD') => collect($customValues)->where('display_position', config('consts.user_custom_items.POSITION_BUSINESS_CUSTOM_FIELD'))
            ]
        ];


        // 各種定数値。タブ毎にセット
        $consts = [
            // 顧客
            config('consts.business_users.TAB_CUSTOMER_INFO') => [
                // カスタムフィールド表示位置
                'customFieldPositions' => [
                    'custom' => config('consts.user_custom_items.POSITION_BUSINESS_CUSTOM_FIELD')
                ]
            ],
            // 利用履歴
            config('consts.business_users.TAB_USAGE_HISTORY') => [
                'estimateNormalCreateUrl' => route('staff.asp.estimates.normal.create', [$agencyAccount]),
            ],
            // 相談
            config('consts.business_users.TAB_CONSULTATION') =>
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
            // 法人顧客
            config('consts.business_users.TAB_CUSTOMER_INFO') => [
                'read' => Auth::user('staff')->can('view', $businessUser), // 閲覧権限
                'update' => Auth::user('staff')->can('update', $businessUser), // 更新権限
                'delete' => Auth::user('staff')->can('delete', $businessUser), // 削除権限
            ],
            // 利用履歴
            config('consts.business_users.TAB_USAGE_HISTORY') => [
                'create' => Auth::user('staff')->can('create', Reserve::class), // 作成権限
                'read' => Auth::user('staff')->can('viewAny', $businessUser), // 閲覧権限
            ],
            // 法人相談
            config('consts.business_users.TAB_CONSULTATION') => [
                'create' => Auth::user('staff')->can('create', AgencyConsultation::class), // 作成権限
                'read' => Auth::user('staff')->can('viewAny', AgencyConsultation::class), // 閲覧権限
            ]
        ];

        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agencyAccount);

        $view->with(compact(
            'defaultValue',
            'customValues',
            'formSelects',
            'defaultTab',
            'tabCodes',
            'permission',
            'customFields',
            'consts',
            'jsVars',
        ));
    }
}
