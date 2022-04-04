<?php
namespace App\Http\ViewComposers\Staff\ReserveEstimateItinerary;

use App\Models\ReservePurchasingSubject;
use App\Services\CityService;
use App\Services\ReserveEstimateService;
use App\Services\ReserveParticipantOptionPriceService;
use App\Services\ReservePurchasingSubjectHotelService;
use App\Services\ReserveService;
use App\Services\SubjectAirplaneService;
use App\Services\SubjectHotelService;
use App\Services\SubjectOptionService;
use App\Services\SupplierService;
use App\Services\UserCustomItemService;
use App\Services\PriceRelatedChangeService;
use App\Traits\ConstsTrait;
use App\Traits\JsConstsTrait;
use App\Traits\ReserveItineraryTrait;
use App\Traits\UserCustomItemTrait;
use Illuminate\Support\Arr;
use Illuminate\View\View;
use Storage;
use App\Traits\ReserveTrait;

/**
 * 編集ページで使う選択項目などを提供するViewComposer
 */
class EditFormComposer
{
    use UserCustomItemTrait, ConstsTrait, ReserveItineraryTrait, JsConstsTrait, ReserveTrait;
    
    public function __construct(ReserveService $reserveService, UserCustomItemService $userCustomItemService, SupplierService $supplierService, ReserveParticipantOptionPriceService $reserveParticipantOptionPriceService, CityService $cityService, SubjectHotelService $subjectHotelService, SubjectOptionService $subjectOptionService, SubjectAirplaneService $subjectAirplaneService, ReservePurchasingSubjectHotelService $reservePurchasingSubjectHotelService, ReserveEstimateService $reserveEstimateService, PriceRelatedChangeService $priceRelatedChangeService)
    {
        $this->reserveParticipantOptionPriceService = $reserveParticipantOptionPriceService;
        $this->reserveService = $reserveService;
        $this->supplierService = $supplierService;
        $this->userCustomItemService = $userCustomItemService;
        $this->cityService = $cityService;
        $this->subjectHotelService = $subjectHotelService;
        $this->subjectOptionService = $subjectOptionService;
        $this->subjectAirplaneService = $subjectAirplaneService;
        $this->reservePurchasingSubjectHotelService = $reservePurchasingSubjectHotelService;
        $this->reserveEstimateService = $reserveEstimateService;
        $this->priceRelatedChangeService = $priceRelatedChangeService;
    }

    /**
     * @param View $view
     * @return void
     */
    public function compose(View $view)
    {
        $data = $view->getData(); // controllerにセットされたデータを取得
        $reserveItinerary = Arr::get($data, 'reserveItinerary');

        //////////////////////////////////

        $my = auth("staff")->user();
        $agencyAccount = $my->agency->account;

        // 受付種別
        $reception = config('consts.const.RECEPTION_TYPE_ASP');

        $transitionTab = config('consts.reserves.TAB_RESERVE_DETAIL'); // 戻るリンクに使用するTabパラメータ
        $reserve = $reserveItinerary->reserve;

        $isCanceled = $reserve->is_canceled; // キャンセル予約か否か
        $isEnabled = $reserveItinerary->enabled; // 有効な行程か否か。不要かも

        // POST URLの設定等
        if ($reserve->application_step == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積
            
            $postUrl = route('staff.api.itinerary.update', [
                'agencyAccount' => $agencyAccount,
                'reception' => config('consts.const.RECEPTION_TYPE_ASP'),
                'applicationStep' =>config("consts.reserves.APPLICATION_STEP_DRAFT"),
                'controlNumber' => $reserve->estimate_number,
                'itineraryNumber' => $reserveItinerary->control_number,
            ]);

            $backUrl = route('staff.asp.estimates.normal.show', [
                'agencyAccount' => $agencyAccount,
                'estimateNumber' => $reserve->estimate_number,
                'tab' => $transitionTab
            ]);
        } elseif ($reserve->application_step == config("consts.reserves.APPLICATION_STEP_RESERVE")) { // 予約

            $postUrl = route('staff.api.itinerary.update', [
                'agencyAccount' => $agencyAccount,
                'reception' => config('consts.const.RECEPTION_TYPE_ASP'),
                'applicationStep' =>config("consts.reserves.APPLICATION_STEP_RESERVE"),
                'controlNumber' => $reserve->control_number,
                'itineraryNumber' => $reserveItinerary->control_number,
            ]);

            // 催行済みの場合は催行済み詳細ページへ戻る
            $backUrl = $reserve->is_departed ?
            route('staff.estimates.departed.show', [
                'agencyAccount' => $agencyAccount,
                'reserveNumber' => $reserve->control_number,
                'tab' => $transitionTab
            ]) :
            route('staff.asp.estimates.reserve.show', [
                'agencyAccount' => $agencyAccount,
                'reserveNumber' => $reserve->control_number,
                'tab' => $transitionTab
            ]);
        } else {
            abort(404);
        }


        $travelDates = $this->getTravelDates($reserve); // 旅行日の配列
        $isTravelDates = $travelDates ? true : false; // 旅行日が設定されているか否か

        // 参加者情報
        $participants = array();
        foreach ($reserve->participants as $participant) {
            $participants[] = $this->getPaticipantRow($participant);
        }
        // 参加者ID一覧
        $participantIds = collect($participants)->pluck("participant_id");

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

        // デフォルトデータを作成
        $defaultValue = session()->getOldInput();
        if (!isset($defaultValue['note'])) { // 備考
            $defaultValue['note'] = $reserveItinerary->note;
        }
        if (!isset($defaultValue['updated_at'])) { // 更新日時
            $defaultValue['updated_at'] = $reserveItinerary->updated_at->format('Y-m-d H:i:s');
        }
        if (!isset($defaultValue['price_related_change_at'])) { // 料金関連更新日時
            $defaultValue['price_related_change_at'] = $this->priceRelatedChangeService->getChangeAt($reserve->id);
        }

        foreach ($travelDates as $date) {
            if (!isset($defaultValue['dates'][$date])) {
                $defaultValue['dates'][$date] = []; // POST値がない場合は明示的に初期化
                $dateRow = $reserveItinerary->reserve_travel_dates->firstWhere('travel_date', $date);

                if ($dateRow) {
                    foreach ($dateRow->reserve_schedules as $i => $schedule) {
                        $defaultValue['dates'][$date][$i] = Arr::except($schedule->toArray(), ['reserve_purchasing_subjects', 'reserve_schedule_photos'], []); // 行程種別（スポット経由地等）
                        
                        if ($schedule->type===config('consts.reserve_itineraries.ITINERARY_TYPE_WAYPOINT_IMAGE')) { // 写真付き
                            $photos = Arr::get($schedule, "reserve_schedule_photos", []);
                            foreach ($photos as $pi => $photo) {
                                $defaultValue['dates'][$date][$i]['photos'][$pi] = $photo->toArray();
                            }
                        }

                        // reserve_purchasing_subjectsを一旦空に
                        $defaultValue['dates'][$date][$i]['reserve_purchasing_subjects'] = [];

                        foreach ($schedule->reserve_purchasing_subjects as $j => $subject) {

                            // オプション科目
                            if ($subject->subjectable_type === 'App\Models\ReservePurchasingSubjectOption') {
                                $this->subjectProcessCommon($subject, config('consts.subject_categories.SUBJECT_CATEGORY_OPTION'), $date, $i, $j, $customFields, $participants, $participantIds, $defaultValue);

                            // 航空券科目
                            } elseif ($subject->subjectable_type === 'App\Models\ReservePurchasingSubjectAirplane') {
                                $this->subjectProcessCommon($subject, config('consts.subject_categories.SUBJECT_CATEGORY_AIRPLANE'), $date, $i, $j, $customFields, $participants, $participantIds, $defaultValue);
                            
                            // ホテル科目
                            } elseif ($subject->subjectable_type === 'App\Models\ReservePurchasingSubjectHotel') {
                                $this->subjectProcessCommon($subject, config('consts.subject_categories.SUBJECT_CATEGORY_HOTEL'), $date, $i, $j, $customFields, $participants, $participantIds, $defaultValue);
                            }
                        }
                    }
                }
            }
        }

        $formSelects = [
            'transportations' => get_const_item('reserve_itineraries', 'transportation'),
            'suppliers' => ['' => '---'] + $this->supplierService->getNameSelectByAgencyAccount($agencyAccount, true),
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
            'thumbSBaseUrl' => Storage::disk('s3')->url(config('consts.const.UPLOAD_THUMB_S_DIR')),
            // 移動手段で使う定数
            'transportationTypes' => [
                'default' => config('consts.reserve_itineraries.DEFAULT_TRANSPORTATION'), // デフォルト値
                'others' => config('consts.reserve_itineraries.TRANSPORTATION_OTHERS')
            ],
            // 編集モード定数
            'modes' => config('consts.reserve_itineraries.MODE_LIST'),
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
            'postUrl' => $postUrl,
            'backUrl' => $backUrl,
        ];

        // reactに渡す各種定数
        $jsVars = $this->getJsVars($agencyAccount);

        $view->with(compact('formSelects', 'transitionTab', 'defaultValue', 'consts', 'customFields', 'subjectCustomCategoryCode', 'participants', 'modalInitialValues', 'reserve', 'jsVars', 'reception', 'isCanceled', 'isEnabled', 'isTravelDates'));
    }
}
