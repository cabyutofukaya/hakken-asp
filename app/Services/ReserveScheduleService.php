<?php

namespace App\Services;

use App\Models\ReserveSchedule;
use Illuminate\Support\Collection;
use App\Repositories\ReserveSchedule\ReserveScheduleRepository;
use App\Repositories\Agency\AgencyRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class ReserveScheduleService
{
    public function __construct(AgencyRepository $agencyRepository, ReserveScheduleRepository $reserveScheduleRepository)
    {
        $this->agencyRepository = $agencyRepository;
        $this->reserveScheduleRepository = $reserveScheduleRepository;
    }

    /**
     * 当該日に紐づくスケジュールを取得
     */
    public function getByReserveTravelDateId(int $reserveTravelDateId, array $with = [], array $select = [], bool $getDeleted = false) : Collection
    {
        return $this->reserveScheduleRepository->getWhere([
            'reserve_travel_date_id' => $reserveTravelDateId
        ], $with, $select, $getDeleted);
    }

    /**
     * 旅行日IDにおける該当ID以外を削除
     *
     * @param int $reserveTravelDateId 旅行日ID
     * @param array $notDeleteIds 削除しないID一覧
     * @param boolean $isSoftDelete 論理削除か否か
     */
    public function deleteOtherIdsForTravelDate(int $reserveTravelDateId, array $notDeleteIds, bool $isSoftDelete = true)
    {
        $this->reserveScheduleRepository->deleteOtherIdsForTravelDate($reserveTravelDateId, $notDeleteIds, $isSoftDelete);
    }
}
