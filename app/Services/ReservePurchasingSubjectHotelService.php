<?php

namespace App\Services;

use App\Models\ReservePurchasingSubjectHotel;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use App\Repositories\ReservePurchasingSubjectHotel\ReservePurchasingSubjectHotelRepository;

class ReservePurchasingSubjectHotelService
{
    public function __construct(ReservePurchasingSubjectHotelRepository $reservePurchasingSubjectHotelRepository)
    {
        $this->reservePurchasingSubjectHotelRepository = $reservePurchasingSubjectHotelRepository;
    }

    /**
     * 1件取得
     */
    public function find(int $id, array $with = [], array $select=[], bool $getDeleted = false)
    {
        return $this->reservePurchasingSubjectHotelRepository->find($id, $with, $select, $getDeleted);
    }

    public function create(array $data) : ReservePurchasingSubjectHotel
    {
        return $this->reservePurchasingSubjectHotelRepository->create($data);
    }

    /**
     * 更新or新規
     */
    public function updateOrCreate(array $judgeField, array $data) : ReservePurchasingSubjectHotel
    {
        return $this->reservePurchasingSubjectHotelRepository->updateOrCreate($judgeField, $data);
    }

    /**
     * スケジュールIDにおける該当ID以外を削除
     * 
     * @param int $reserveScheduleId スケジュールID
     * @param array $notDeleteIds 削除しないID一覧
     * @param boolean $isSoftDelete 論理削除か否か
     * @return array 削除したIDリスト
     */
    public function deleteOtherIdsForSchedule(int $reserveScheduleId, array $notDeleteIds, bool $isSoftDelete = true) : array
    {
        return $this->reservePurchasingSubjectHotelRepository->deleteOtherIdsForSchedule($reserveScheduleId, $notDeleteIds, $isSoftDelete);
    }

}
