<?php
namespace App\Http\ViewComposers\Staff\ReserveEstimateItinerary\Pdf;

use App\Services\ParticipantService;
use App\Services\ReserveService;
use Hashids;
use Illuminate\Support\Arr;
use Illuminate\View\View;

/**
 * pdfで使う選択項目などを提供するViewComposer
 */
class RoomingListComposer
{
    public function __construct(ReserveService $reserveService, ParticipantService $participantService)
    {
        $this->reserveService = $reserveService;
        $this->participantService = $participantService;
    }

    /**
     * @param View $view
     * @return void
     */
    public function compose(View $view)
    {
        $data = $view->getData(); // controllerにセットされたデータを取得

        $hotelName = Arr::get($data, 'hotelName');
        $date = Arr::get($data, 'date');
        $roomNumbers = Arr::get($data, 'roomNumbers');
        $participantIds = Arr::get($data, 'participantIds');

        //////////////////////////////////

        ///////////////// 参加者情報を取得 ////////////

        $agencyId = auth('staff')->user()->agency_id;

        // 全参加者情報を取得
        $participants = collect([]);
        if ($participantIds) {
            $participants = $this->participantService->getByIds($participantIds, ['user']);
        }

        // ルームリストデータを作成
        $roomingList = [];
        $roomingList[$date][$hotelName] = [];

        foreach ($roomNumbers as $i => $rm) {
            if (!isset($roomingList[$date][$hotelName][$rm])) {
                $roomingList[$date][$hotelName][$rm] = [];
            }
            $pid = Arr::get($participantIds, $i);

            $participant = $participants->firstWhere('id', $pid);
            if (data_get($participant, "agency_id") == $agencyId) { // 念の為、当該会社以外の参加者データを表示させないように会社IDをチェック
                // 参加者情報を部屋番号ごとにセット
                $roomingList[$date][$hotelName][$rm][] = $participant;
            } else {
                $roomingList[$date][$hotelName][$rm][] = null;
            }
        }

        $view->with(compact('roomingList'));
    }
}
