<?php
namespace App\Http\ViewComposers\Staff\ReserveEstimateItinerary;

use App\Services\CityService;
use App\Services\ReserveService;
use App\Services\ReserveEstimateService;
use App\Services\SupplierService;
use App\Services\SubjectHotelService;
use App\Services\SubjectOptionService;
use App\Services\SubjectAirplaneService;
use App\Services\UserCustomItemService;
use App\Services\ReservePurchasingSubjectHotelService;
use App\Traits\ConstsTrait;
use App\Traits\ReserveItineraryTrait;
use App\Traits\UserCustomItemTrait;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use App\Traits\JsConstsTrait;
use App\Traits\ReserveTrait;


/**
 * 作成ページで使う選択項目などを提供するViewComposer
 */
class CreateFormComposer
{
    use UserCustomItemTrait, ConstsTrait, ReserveItineraryTrait, JsConstsTrait, ReserveTrait;
    
    public function __construct(ReserveService $reserveService, UserCustomItemService $userCustomItemService, SupplierService $supplierService, CityService $cityService, SubjectHotelService $subjectHotelService, SubjectOptionService $subjectOptionService, SubjectAirplaneService $subjectAirplaneService, ReservePurchasingSubjectHotelService $reservePurchasingSubjectHotelService, ReserveEstimateService $reserveEstimateService)
    {
        $this->cityService = $cityService;
        $this->reserveService = $reserveService;
        $this->supplierService = $supplierService;
        $this->userCustomItemService = $userCustomItemService;
        $this->subjectHotelService = $subjectHotelService;
        $this->subjectOptionService = $subjectOptionService;
        $this->subjectAirplaneService = $subjectAirplaneService;
        $this->reservePurchasingSubjectHotelService = $reservePurchasingSubjectHotelService;
        $this->reserveEstimateService = $reserveEstimateService;
    }

    /**
     * @param View $view
     * @return void
     */
    public function compose(View $view)
    {
        $data = $view->getData(); // controllerにセットされたデータを取得
        $reserve = Arr::get($data, 'reserve');

        //////////////////////////////////

        $my = auth("staff")->user();
        $agencyAccount = $my->agency->account;

        // 受付種別
        $reception = config('consts.const.RECEPTION_TYPE_ASP');

        $transitionTab = config('consts.reserves.TAB_RESERVE_DETAIL'); // 戻るリンクに使用するTabパラメータ

        // POST URLの設定等
        if ($reserve->application_step == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積
            
            $storeUrl = route('staff.asp.estimates.itinerary.store', [
                'agencyAccount' => $agencyAccount, 
                'applicationStep' =>config("consts.reserves.APPLICATION_STEP_DRAFT"), 
                'controlNumber' => $reserve->estimate_number
            ]);

            $backUrl = route('staff.asp.estimates.normal.show', [
                'agencyAccount' => $agencyAccount, 
                'estimateNumber' => $reserve->estimate_number,
                'tab' => $transitionTab
            ]);

        } elseif ($reserve->application_step == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約

            $storeUrl = route('staff.asp.estimates.itinerary.store', [
                'agencyAccount' => $agencyAccount, 
                'applicationStep' =>config("consts.reserves.APPLICATION_STEP_RESERVE"), 
                'controlNumber' => $reserve->control_number
            ]);

            $backUrl = route('staff.asp.estimates.reserve.show', [
                'agencyAccount' => $agencyAccount, 
                'reserveNumber' => $reserve->control_number,
                'tab' => $transitionTab
            ]);

        } else {
            abort(404);
        }

        $travelDates = $this->getTravelDates($reserve);

        // 参加者情報
        $participants = array();
        foreach ($reserve->participants as $participant) {
            $participants[] = $this->getPaticipantRow($participant);
        }

        // 科目に設定されたカスタム項目情報を取得
        
        $subjectCustomCategoryCode = config('consts.user_custom_categories.CUSTOM_CATEGORY_SUBJECT');

        $subjectCustomItems = $this->userCustomItemService->getByCategoryCodeForAgencyAccount(
            $subjectCustomCategoryCode,
            $agencyAccount,
            true,
            [],
            [
                'user_custom_items.id',
                'user_custom_items.code',
                'user_custom_items.key',
                'user_custom_items.type',
                'user_custom_items.name',
                'user_custom_items.input_type',
                'user_custom_items.list',
                'user_custom_items.display_position',
                'user_custom_items.unedit_item',
            ] // 取得カラムを指定。joinするので対象レコードを明示的に指定
        );

        // カスタム項目。科目 ごとに取得（モーダル）
        $customFields = [
            // オプション
            config('consts.subject_categories.SUBJECT_CATEGORY_OPTION') =>
                $subjectCustomItems->where('display_position', config('consts.user_custom_items.POSITION_SUBJECT_OPTION')),
            // 航空券科目
            config('consts.subject_categories.SUBJECT_CATEGORY_AIRPLANE') =>
                $subjectCustomItems->where('display_position', config('consts.user_custom_items.POSITION_SUBJECT_AIRPLANE')),
            // ホテル科目
            config('consts.subject_categories.SUBJECT_CATEGORY_HOTEL') =>
                $subjectCustomItems->where('display_position', config('consts.user_custom_items.POSITION_SUBJECT_HOTEL'))
        ];


        $defaultValue = session()->getOldInput();
        foreach ($travelDates as $date) {
            if (!isset($defaultValue['dates'][$date])) {
                $defaultValue['dates'][$date] = []; // POST値がない場合は明示的に初期化
            }
        }

        $formSelects = [
            'transportations' => get_const_item('reserve_itineraries', 'transportation'),
            'subjectCategories' => get_const_item('subject_categories', 'subject_category'), // 科目カテゴリ
            'suppliers' => ['' => '---'] + $this->supplierService->getNameSelectByAgencyAccount($agencyAccount, false),
            'cities' => ['' => '---'] + $this->cityService->getNameSelectByAgencyAccount($agencyAccount),
            'zeiKbns' => get_const_item('subject_categories', 'zei_kbn'),
            // 商品名選択。label/value/nameを初期化
            'defaultSubjectHotels' => $this->subjectHotelService->getDefaultOptions($my->agency_id, 30, ['label' => '---', 'value' => '', 'name' => '']),
            'defaultSubjectOptions' => $this->subjectOptionService->getDefaultOptions($my->agency_id, 30, ['label' => '---', 'value' => '', 'name' => '']),
            'defaultSubjectAirplanes' => $this->subjectAirplaneService->getDefaultOptions($my->agency_id, 30, ['label' => '---', 'value' => '', 'name' => '']),
        ];

        // モーダル内で利用する初期値
        $modalInitialValues = [
            'zeiKbnDefault' => config('consts.subject_categories.ZEI_KBN_DEFAULT')
        ];

        $consts = [
            // 移動手段で使う定数
            'transportationTypes' => [
                'default' => config('consts.reserve_itineraries.DEFAULT_TRANSPORTATION'), // デフォルト値
                'others' => config('consts.reserve_itineraries.TRANSPORTATION_OTHERS')
            ],
            // 旅程種別定数
            'itineraryTypes' => [
                'waypoint' => config('consts.reserve_itineraries.ITINERARY_TYPE_WAYPOINT'), // スポット・経由地
                'waypoint_image' => config('consts.reserve_itineraries.ITINERARY_TYPE_WAYPOINT_IMAGE'), // スポット・経由地（写真付き）
                'destination' => config('consts.reserve_itineraries.ITINERARY_TYPE_DESTINATION'), // 宿泊地・目的地
            ],
            // 科目タイプ
            'subjectCategoryTypes' => [
                'default' => config('consts.subject_categories.DEFAULT_SUBJECT_CATEGORY'), // デフォルト値
                'option' => config('consts.subject_categories.SUBJECT_CATEGORY_OPTION'), // オプション
                'airplane' => config('consts.subject_categories.SUBJECT_CATEGORY_AIRPLANE'), // 航空券
                'hotel' => config('consts.subject_categories.SUBJECT_CATEGORY_HOTEL'), // ホテル
            ],
            // 年齢区分
            'ageKbns' => [
                'ad' => config('consts.users.AGE_KBN_AD'),
                'ch' => config('consts.users.AGE_KBN_CH'),
                'inf' => config('consts.users.AGE_KBN_INF'),
            ],
            // カスタム項目タイプ
            'customFieldTypes' => $this->getCustomFieldTypes(),
            // カスタム項目入力タイプ
            'customFieldInputTypes' => $this->getCustomFieldInputTypes(),
            // カスタム項目管理コード
            'customFieldCodes' => [
                'subject_option_kbn' => config('consts.user_custom_items.CODE_SUBJECT_OPTION_KBN'),
                'subject_hotel_kbn' => config('consts.user_custom_items.CODE_SUBJECT_HOTEL_KBN'),
                'subject_hotel_room_type' => config('consts.user_custom_items.CODE_SUBJECT_HOTEL_ROOM_TYPE'),
                'subject_hotel_meal_type' => config('consts.user_custom_items.CODE_SUBJECT_HOTEL_MEAL_TYPE'),
                'subject_airplane_company' => config('consts.user_custom_items.CODE_SUBJECT_AIRPLANE_COMPANY'),
            ],
            'application_step_list' => [
                'application_step_draft' => config('consts.reserves.APPLICATION_STEP_DRAFT'),
                'application_step_reserve' => config('consts.reserves.APPLICATION_STEP_RESERVE'),
            ],
        ];

        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agencyAccount);

        $view->with(compact('formSelects', 'transitionTab', 'defaultValue', 'consts', 'customFields', 'subjectCustomCategoryCode', 'participants', 'modalInitialValues', 'storeUrl', 'backUrl', 'jsVars', 'reception'));
    }
}
