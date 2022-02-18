<?php

namespace App\Services;

use App\Models\ReserveTravelDate;
use Illuminate\Support\Collection;
use App\Repositories\ReserveTravelDate\ReserveTravelDateRepository;
use App\Repositories\Agency\AgencyRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class ReserveTravelDateService
{
    public function __construct(AgencyRepository $agencyRepository, ReserveTravelDateRepository $reserveTravelDateRepository)
    {
        $this->agencyRepository = $agencyRepository;
        $this->reserveTravelDateRepository = $reserveTravelDateRepository;
    }

    /**
     * 旅行ID、旅行日から当該レコードを取得
     */
    public function getByReserveTravelDate(int $reserveId, string $travelDate) : Collection
    {
        return $this->reserveTravelDateRepository->getWhere([
            'reserve_id' => $reserveId,
            'travel_date' => $travelDate
        ]);
    }

    /**
     * 行程IDにおける該当ID以外を削除
     * 
     * @param int $reserveItineraryId 行程ID
     * @param array $notDeleteIds 削除しないID一覧
     * @param boolean $isSoftDelete 論理削除か否か
     */
    public function deleteOtherIdsForReserveItinerary(int $reserveItineraryId, array $notDeleteIds, bool $isSoftDelete = true)
    {
        $this->reserveTravelDateRepository->deleteOtherIdsForReserveItinerary($reserveItineraryId, $notDeleteIds, $isSoftDelete);
    }

    /**
     * 行程日を削除
     * 
     * @param int $reserveItineraryId 行程ID
     * @param array $deleteDays 削除日の配列
     */
    public function deleteForItineraryDays(int $reserveItineraryId, array $deleteDays, bool $isSoftDelete = true) : bool
    {
        return $this->reserveTravelDateRepository->deleteForItineraryDays($reserveItineraryId, $deleteDays, $isSoftDelete);
    }

}
