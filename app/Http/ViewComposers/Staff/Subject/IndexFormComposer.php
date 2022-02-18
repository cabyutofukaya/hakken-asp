<?php
namespace App\Http\ViewComposers\Staff\Subject;

use App\Services\CityService;
use App\Services\SubjectCategoryService;
use App\Services\SupplierService;
use App\Services\UserCustomCategoryService;
use App\Services\UserCustomItemService;
use App\Traits\JsConstsTrait;
use Illuminate\Support\Arr;
use Illuminate\View\View;

/**
 * トップページに使う選択項目などを提供するViewComposer
 */
class IndexFormComposer
{
    use JsConstsTrait;

    public function __construct(CityService $cityService, SupplierService $supplierService, UserCustomItemService $userCustomItemService, SubjectCategoryService $subjectCategoryService, UserCustomCategoryService $userCustomCategoryService)
    {
        $this->cityService = $cityService;
        $this->supplierService = $supplierService;
        $this->userCustomItemService = $userCustomItemService;
        $this->subjectCategoryService = $subjectCategoryService;
        $this->userCustomCategoryService = $userCustomCategoryService;
    }

    /**
     * @param View $view
     * @return void
     */
    public function compose(View $view)
    {
        $agencyAccount = request()->agencyAccount;

        $defaultTab = request()->input('tab', config('consts.subject_categories.DEFAULT_SUBJECT_CATEGORY'));

        // 「科目マスタ」のカスタムカテゴリIDを取得
        $customCategoryCode = config('consts.user_custom_categories.CUSTOM_CATEGORY_SUBJECT');

        // 科目マスタ 管理コード一覧
        $subjectCategoryCodes = config('consts.subject_categories.SUBJECT_CATEGORY_LIST');

        // 新規登録リンク
        $createLinks = [
            config('consts.subject_categories.SUBJECT_CATEGORY_OPTION') => route('staff.master.subject.create', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.subject_categories.SUBJECT_CATEGORY_OPTION')]), // オプション科目
            config('consts.subject_categories.SUBJECT_CATEGORY_AIRPLANE') => route('staff.master.subject.create', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.subject_categories.SUBJECT_CATEGORY_AIRPLANE')]), // 航空券科目
            config('consts.subject_categories.SUBJECT_CATEGORY_HOTEL') => route('staff.master.subject.create', ['agencyAccount' => $agencyAccount, 'tab' => config('consts.subject_categories.SUBJECT_CATEGORY_HOTEL')]), // ホテル科目
        ];

        // ユーザーカスタムアイテム種別一覧
        $customItemTypes = config('consts.user_custom_items.CUSTOM_ITEM_LIST');

        $agencyAccount = auth('staff')->user()->agency->account;


        // 科目マスタごとのカスタム項目を取得
        $userCustomFields = [];
        foreach (array_values(config('consts.subject_categories.SUBJECT_CATEGORY_LIST')) as $subjectCategoryCode) {

            // 項目マスタコードに対応した表示位置を求める
            $position = null;
            if ($subjectCategoryCode === config('consts.subject_categories.SUBJECT_CATEGORY_OPTION')) {
                $position = config('consts.user_custom_items.POSITION_SUBJECT_OPTION');
            } elseif ($subjectCategoryCode === config('consts.subject_categories.SUBJECT_CATEGORY_AIRPLANE')) {
                $position = config('consts.user_custom_items.POSITION_SUBJECT_AIRPLANE');
            } elseif ($subjectCategoryCode === config('consts.subject_categories.SUBJECT_CATEGORY_HOTEL')) {
                $position = config('consts.user_custom_items.POSITION_SUBJECT_HOTEL');
            }
            
            if (is_null($position)) {
                continue;
            }

            $row = $this->userCustomItemService->getByCategoryCodeForAgencyAccount(
                config('consts.user_custom_categories.CUSTOM_CATEGORY_SUBJECT'), 
                $agencyAccount, 
                true, 
                [], 
                [
                    'key',
                    'user_custom_items.name',
                    'user_custom_items.code',
                    'user_custom_items.input_type',
                    'user_custom_items.type',
                    'user_custom_items.list',
                ],
                [
                    'display_position' => $position
                ]
                )->map(function ($row, $key) {
                    $row['select_item'] = $row->type === config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST') ? $row->select_item([''=>'すべて']) : null; // リスト項目の場合はselectメニュー項目をセット
                    return $row;
                });
            $userCustomFields[$subjectCategoryCode] = $row->toArray();
        }

        $formSelects = [
            'cities' => ['' => 'すべて'] + $this->cityService->getNameSelectByAgencyAccount($agencyAccount),
            'suppliers' => ['' => 'すべて'] + $this->supplierService->getNameSelectByAgencyAccount($agencyAccount),
            'userCustomFields' => $userCustomFields,
        ];

        $consts = [
            // カスタム項目管理コード
            'customFieldCodes' => [
                'subject_option_kbn' => config('consts.user_custom_items.CODE_SUBJECT_OPTION_KBN'),
                'subject_hotel_kbn' => config('consts.user_custom_items.CODE_SUBJECT_HOTEL_KBN'),
                'subject_hotel_room_type' => config('consts.user_custom_items.CODE_SUBJECT_HOTEL_ROOM_TYPE'),
                'subject_hotel_meal_type' => config('consts.user_custom_items.CODE_SUBJECT_HOTEL_MEAL_TYPE'),
                'subject_airplane_company' => config('consts.user_custom_items.CODE_SUBJECT_AIRPLANE_COMPANY'),
            ],

        ];

        // 認可情報
        $permission = [
            'option' => [
                'create' => \Auth::user('staff')->can('create', new \App\Models\SubjectOption), // 作成権限
            ],
            'airplane' => [
                'create' => \Auth::user('staff')->can('create', new \App\Models\SubjectAirplane), // 作成権限
            ],
            'hotel' => [
                'create' => \Auth::user('staff')->can('create', new \App\Models\SubjectHotel), // 作成権限
            ]
        ];

        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agencyAccount);

        $view->with(compact('formSelects', 'createLinks', 'customCategoryCode', 'customItemTypes', 'subjectCategoryCodes', 'defaultTab', 'consts', 'jsVars', 'permission'));
    }
}
