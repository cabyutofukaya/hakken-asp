<?php

namespace App\Services;

use App\Models\ReservePurchasingSubjectOption;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use App\Repositories\ReservePurchasingSubjectOption\ReservePurchasingSubjectOptionRepository;

class ReservePurchasingSubjectOptionService
{
    public function __construct(ReservePurchasingSubjectOptionRepository $reservePurchasingSubjectOptionRepository)
    {
        $this->reservePurchasingSubjectOptionRepository = $reservePurchasingSubjectOptionRepository;
    }

    /**
     * idから一件取得
     */
    public function find(int $id, array $with = [], array $select=[], bool $getDeleted = false) : ReservePurchasingSubjectOption
    {
        return $this->reservePurchasingSubjectOptionRepository->find($id, $with, $select, $getDeleted);
    }

    public function create(array $data) : ReservePurchasingSubjectOption
    {
        return $this->reservePurchasingSubjectOptionRepository->create($data);
    }

    /**
     * 更新or新規
     */
    public function updateOrCreate(array $judgeField, array $data) : ReservePurchasingSubjectOption
    {
        return $this->reservePurchasingSubjectOptionRepository->updateOrCreate($judgeField, $data);
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
        return $this->reservePurchasingSubjectOptionRepository->deleteOtherIdsForSchedule($reserveScheduleId, $notDeleteIds, $isSoftDelete);
    }
}
