<?php
namespace App\Repositories\ReservePurchasingSubjectOption;

use App\Models\ReservePurchasingSubjectOption;
use Illuminate\Support\Collection;

class ReservePurchasingSubjectOptionRepository implements ReservePurchasingSubjectOptionRepositoryInterface
{
    public function __construct(ReservePurchasingSubjectOption $reservePurchasingSubjectOption)
    {
        $this->reservePurchasingSubjectOption = $reservePurchasingSubjectOption;
    }

    public function create(array $data) : ReservePurchasingSubjectOption
    {
        return $this->reservePurchasingSubjectOption->create($data);
    }

    public function find(int $id, array $with = [], array $select=[], bool $getDeleted = false) : ReservePurchasingSubjectOption
    {
        $query = $this->reservePurchasingSubjectOption;
        $query = $select ? $query->select($select) : $query;
        $query = $with ? $query->with($with) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        return $query->findOrFail($id);
    }

    public function updateOrCreate(array $judgeField, array $data) : ReservePurchasingSubjectOption
    {
        return $this->reservePurchasingSubjectOption->updateOrCreate($judgeField, $data);
    }

    /**
     * スケジュールIDにおける該当ID以外を削除
     * 
     * @param int $reserveScheduleId スケジュールID
     * @param array $notDeleteIds 削除しないID一覧
     * @param boolean $isSoftDelete 論理削除か否か
     */
    public function deleteOtherIdsForSchedule(int $reserveScheduleId, array $notDeleteIds, bool $isSoftDelete = true) : array
    {
        $res = [];

        foreach ($this->reservePurchasingSubjectOption->where('reserve_schedule_id', $reserveScheduleId)->whereNotIn('id', $notDeleteIds)->get() as $row) {
            // 1行ずつ削除しないと、Modelのstatic::deleting が呼ばれないようなのでforeachで1行ずつ処理
            $res[] = $row->id;// 削除IDを記録
            if ($isSoftDelete) {
                $row->delete();
            } else {
                $row->forceDelete();
            }
        }
        return $res;

    }
}
