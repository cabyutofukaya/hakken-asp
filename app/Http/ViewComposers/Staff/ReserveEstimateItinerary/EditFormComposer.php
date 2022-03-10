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
    
    public function __construct(ReserveService $reserveService, UserCustomItemService $userCustomItemService, SupplierService $supplierService, ReserveParticipantOptionPriceService $reserveParticipantOptionPriceService, CityService $cityService, SubjectHotelService $subjectHotelService, SubjectOptionService $subjectOptionService, SubjectAirplaneService $subjectAirplaneService, ReservePurchasingSubjectHotelService $reservePurchasingSubjectHotelService, ReserveEstimateService $reserveEstimateService)
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
        $isEnabled = $reserveItinerary->enabled; // 有効な行程か否か

        // POST URLの設定等
        if ($reserve->application_step == config("consts.reserves.APPLICATION_STEP_DRAFT")) { // 見積
            
            $updateUrl = route('staff.asp.estimates.itinerary.update', [
                'agencyAccount' => $agencyAccount, 
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

            $updateUrl = route('staff.asp.estimates.itinerary.update', [
                'agencyAccount' => $agencyAccount, 
                'applicationStep' =>config("consts.reserves.APPLICATION_STEP_RESERVE"), 
                'controlNumber' => $reserve->control_number,
                'itineraryNumber' => $reserveItinerary->control_number,
            ]);

            $backUrl = route('staff.asp.estimates.reserve.show', [
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
            'subjectCategories' => get_const_item('subject_categories', 'subject_category'), // 科目カテゴリ
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

        $view->with(compact('formSelects', 'transitionTab', 'defaultValue', 'consts', 'customFields', 'subjectCustomCategoryCode', 'participants', 'modalInitialValues', 'reserve', 'updateUrl', 'backUrl','jsVars', 'reception', 'isCanceled', 'isEnabled', 'isTravelDates'));
    }

    /**
     * 科目データに関する共通処理（オプション、ホテル、航空）
     *
     * @param ReservePurchasingSubject $subject
     * @param string $subjectCagetory 科目値
     * @param date $date 旅行日
     * @param int $i 行程番号
     * @param int $j 仕入番号
     * @param array $customFields カスタム項目情報
     * @param Collection $participantIds 参加者ID情報
     * @param array $defaultValue form入力値
     */
    private function subjectProcessCommon(ReservePurchasingSubject $subject, string $subjectCagetory, string $date, int $i, int $j, array $customFields, array $participants, $participantIds, &$defaultValue)
    {
        // 当該レコードに設定されたカスタム項目値
        $vReservePurchasingSubjectCustomValues = $subject->subjectable->v_reserve_purchasing_subject_custom_values;
        $userCustomItem = array();
        foreach ($customFields[$subjectCagetory]->pluck('key') as $key) {
            $row = $vReservePurchasingSubjectCustomValues->firstWhere('key', $key);
            $userCustomItem[$key] = Arr::get($row, 'val'); // val値をセット
        }
        ////////////////

        $defaultValue['dates'][$date][$i]['reserve_purchasing_subjects'][$j] = array_merge(
            Arr::except($subject->subjectable->toArray(), ['reserve_participant_prices','v_reserve_purchasing_subject_custom_values'], []),
            $userCustomItem,
            ['mode' => 'EDIT', 'subject' => $subjectCagetory, 'id' => $subject->subjectable ? $subject->subjectable->id : null]
        ); // 仕入科目。保存データ・カスタム項目・定数データ(mode等)を結合して初期化。mode=新規or編集、subject=科目種別

        // participantを一旦空に
        $defaultValue['dates'][$date][$i]['reserve_purchasing_subjects'][$j]['participants'] = [];

        foreach ($subject->subjectable->reserve_participant_prices as $price) {
            if (($k = array_search($price->participant->id, $participantIds->toArray())) !== false) { // キーはparticipantIdsの順番でマッピング
                $defaultValue['dates'][$date][$i]['reserve_purchasing_subjects'][$j]['participants'][$k] = array_merge(
                    Arr::except($price->toArray(), ['participant'], []),
                    ['participant_id' => $price->participant->id],
                    ['age_kbn' => Arr::get($price->toArray(), 'participant.age_kbn')] // 年齢区分はparticipantsに保持されている
                ); // 料金明細
            }
        }

        /**
         * 追加された参加者をチェックして
         * 初期値をセットする
         */
        // diffの引数は当該仕入明細の参加者としてDBに保存されいているID一覧
        $newParticipationIds = $participantIds->diff($subject->subjectable->reserve_participant_prices->map(function ($item) {
            return $item->participant->id;
        }));

        if ($newParticipationIds->isNotEmpty()) { // 追加参加者あり
            foreach ($newParticipationIds->all() as $pid) {
                if (($l = array_search($pid, $participantIds->toArray())) !== false) { // キーはparticipantIdsの順番でマッピング

                    $targetParticipant = collect($participants)->firstWhere('participant_id', $pid);

                    // reserve_itinerary-create-edit.jsのinitialTargetPurchasingと同様
                    $defaultValue['dates'][$date][$i]['reserve_purchasing_subjects'][$j]['participants'][$l] = array_merge(
                        $subjectCagetory == config('consts.subject_categories.SUBJECT_CATEGORY_HOTEL') ? ['room_number' => null] : [], // ホテル科目の場合はルーム番号も初期化
                        $this->reserveParticipantOptionPriceService->getInitialData($pid, $targetParticipant['age_kbn'], $subject->subjectable->toArray())
                    ); // 料金明細
                }
            }
        }
    }
}
