<?php
namespace App\Http\ViewComposers\Staff\ReserveEstimateItinerary\Pdf;

use App\Services\ParticipantService;
use App\Services\ReserveParticipantOptionPriceService;
use App\Services\ReserveService;
use App\Services\SupplierService;
use App\Services\UserCustomItemService;
use Illuminate\Support\Arr;
use Illuminate\View\View;

/**
 * pdfで使う選択項目などを提供するViewComposer
 */
class ItineraryRoomingListComposer
{
    public function __construct(ReserveService $reserveService, UserCustomItemService $userCustomItemService, SupplierService $supplierService, ReserveParticipantOptionPriceService $reserveParticipantOptionPriceService, ParticipantService $participantService)
    {
        $this->participantService = $participantService;
        $this->reserveParticipantOptionPriceService = $reserveParticipantOptionPriceService;
        $this->reserveService = $reserveService;
        $this->supplierService = $supplierService;
        $this->userCustomItemService = $userCustomItemService;
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

        ///////////////// 参加者情報を取得 ////////////

        $roomingList = [];

        foreach ($reserveItinerary->reserve_travel_dates as $reserveTravelDate) {

            if (!isset($roomingList[$reserveTravelDate->travel_date])) {
                $roomingList[$reserveTravelDate->travel_date] = [];
            }

            foreach ($reserveTravelDate->reserve_schedules as $reserveSchedule) {
                foreach ($reserveSchedule->reserve_purchasing_subject_hotels as $reservePurchasingSubjectHotel) {
                    if (!isset($roomingList[$reserveTravelDate->travel_date][$reservePurchasingSubjectHotel->hotel_name])) {
                        $roomingList[$reserveTravelDate->travel_date][$reservePurchasingSubjectHotel->hotel_name] = [];
                    }
                    // 部屋タイプ情報
                    $roomType = data_get($reservePurchasingSubjectHotel, 'room_types.0.val', '');
                    if (!isset($roomingList[$reserveTravelDate->travel_date][$reservePurchasingSubjectHotel->hotel_name][$roomType])) {
                        $roomingList[$reserveTravelDate->travel_date][$reservePurchasingSubjectHotel->hotel_name][$roomType] = [];
                    }

                    foreach ($reservePurchasingSubjectHotel->reserve_participant_prices as $reserveParticipantPrice) {

                        // 部屋タイプと部屋番号の区別で分ける
                        if (!isset($roomingList[$reserveTravelDate->travel_date][$reservePurchasingSubjectHotel->hotel_name][$roomType][$reserveParticipantPrice->room_number])) {
                            $roomingList[$reserveTravelDate->travel_date][$reservePurchasingSubjectHotel->hotel_name][$roomType][$reserveParticipantPrice->room_number] = [];
                        }
                        if ($reserveParticipantPrice->valid) {
                            $roomingList[$reserveTravelDate->travel_date][$reservePurchasingSubjectHotel->hotel_name][$roomType][$reserveParticipantPrice->room_number][] = $reserveParticipantPrice->participant;
                        }
                    }
                }
            }
        }
        $view->with(compact('roomingList'));
    }
}
