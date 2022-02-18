<?php

namespace App\Services;

use App\Models\ReservePurchasingSubjectAirplane;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use App\Repositories\ReservePurchasingSubjectAirplane\ReservePurchasingSubjectAirplaneRepository;

class ReservePurchasingSubjectAirplaneService
{
    public function __construct(ReservePurchasingSubjectAirplaneRepository $reservePurchasingSubjectAirplaneRepository)
    {
        $this->reservePurchasingSubjectAirplaneRepository = $reservePurchasingSubjectAirplaneRepository;
    }


    /**
     * 1件取得
     */
    public function find(int $id, array $with = [], array $select=[], bool $getDeleted = false)
    {
        return $this->reservePurchasingSubjectAirplaneRepository->find($id, $with, $select, $getDeleted);
    }

    public function create(array $data) : ReservePurchasingSubjectAirplane
    {
        return $this->reservePurchasingSubjectAirplaneRepository->create($data);
    }

    /**
     * 更新or新規
     */
    public function updateOrCreate(array $judgeField, array $data) : ReservePurchasingSubjectAirplane
    {
        return $this->reservePurchasingSubjectAirplaneRepository->updateOrCreate($judgeField, $data);
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
        return $this->reservePurchasingSubjectAirplaneRepository->deleteOtherIdsForSchedule($reserveScheduleId, $notDeleteIds, $isSoftDelete);
    }
}
