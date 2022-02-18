<?php
namespace App\Repositories\ReserveSchedule;

use App\Models\ReserveSchedule;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ReserveScheduleRepository implements ReserveScheduleRepositoryInterface
{
    /**
    * @param object $reserveSchedule
    */
    public function __construct(ReserveSchedule $reserveSchedule)
    {
        $this->reserveSchedule = $reserveSchedule;
    }

    /**
     * 条件で全取得
     */
    public function getWhere(array $where, array $with = [], array $select = [], bool $getDeleted = false): Collection
    {
        $query = $this->reserveSchedule;
        $query = $getDeleted ? $query->withTrashed() : $query;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;

        foreach ($where as $key => $val) {
            $query = $query->where($key, $val);
        }
        return $query->get();
    }

    /**
     * 旅行日IDにおける該当ID以外を削除
     * 
     * @param int $reserveTravelDateId 旅行日ID
     * @param array $notDeleteIds 削除しないID一覧
     * @param boolean $isSoftDelete 論理削除か否か
     */
    public function deleteOtherIdsForTravelDate(int $reserveTravelDateId, array $notDeleteIds, bool $isSoftDelete = true) : bool
    {
        foreach ($this->reserveSchedule->with(['reserve_purchasing_subjects.subjectable','reserve_schedule_photos'])->where('reserve_travel_date_id', $reserveTravelDateId)->whereNotIn('id', $notDeleteIds)->get() as $row) {
            // 1行ずつ削除しないと、Modelのstatic::deleting が呼ばれないようなのでforeachで1行ずつ処理
            if ($isSoftDelete) {
                $row->delete();
            } else {
                $row->forceDelete();
            }
        }
        return true;
    }
}