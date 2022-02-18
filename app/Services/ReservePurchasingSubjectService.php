<?php

namespace App\Services;

use App\Models\ReservePurchasingSubject;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use App\Repositories\ReservePurchasingSubject\ReservePurchasingSubjectRepository;

class ReservePurchasingSubjectService
{
    public function __construct(ReservePurchasingSubjectRepository $reservePurchasingSubjectRepository)
    {
        $this->reservePurchasingSubjectRepository = $reservePurchasingSubjectRepository;
    }

    public function create(array $data): ReservePurchasingSubject
    {
        return $this->reservePurchasingSubjectRepository->create($data);
    }

    /**
     * 更新or新規
     */
    public function updateOrCreate(array $judgeField, array $data) : ReservePurchasingSubject
    {
        return $this->reservePurchasingSubjectRepository->updateOrCreate($judgeField, $data);
    }

    /**
     * スケジュールID紐づく科目における該当ID以外を削除
     * 
     * @param int $reserveScheduleId スケジュールID
     * @param string $subjectableType 科目モデル名
     * @param array $notDeleteSubjectableIds 削除しない科目ID一覧
     * @param boolean $isSoftDelete 論理削除か否か
     */
    public function deleteOtherSubjectableIdsForSchedule(int $reserveScheduleId, string $subjectableType, array $notDeleteSubjectableIds, bool $isSoftDelete = true)
    {
        $this->reservePurchasingSubjectRepository->deleteOtherSubjectableIdsForSchedule($reserveScheduleId, $subjectableType, $notDeleteSubjectableIds, $isSoftDelete);
    }

    /**
     * 当該日程IDに対して出金履歴がある場合はtrue
     */
    public function existWithdrawalHistoryByReserveScheduleId(int $reserveScheduleId)
    {
        return $this->reservePurchasingSubjectRepository->existWithdrawalHistoryByReserveScheduleId($reserveScheduleId);
    }
}
