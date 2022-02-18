<?php
namespace App\Repositories\ReservePurchasingSubjectHotel;

use App\Models\ReservePurchasingSubjectHotel;
use Illuminate\Support\Collection;

class ReservePurchasingSubjectHotelRepository implements ReservePurchasingSubjectHotelRepositoryInterface
{
    public function __construct(ReservePurchasingSubjectHotel $reservePurchasingSubjectHotel)
    {
        $this->reservePurchasingSubjectHotel = $reservePurchasingSubjectHotel;
    }

    public function find(int $id, array $with = [], array $select=[], bool $getDeleted = false) : ReservePurchasingSubjectHotel
    {
        $query = $this->reservePurchasingSubjectHotel;
        $query = $select ? $query->select($select) : $query;
        $query = $with ? $query->with($with) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        return $query->findOrFail($id);
    }

    public function create(array $data) : ReservePurchasingSubjectHotel
    {
        return $this->reservePurchasingSubjectHotel->create($data);
    }

    public function updateOrCreate(array $judgeField, array $data) : ReservePurchasingSubjectHotel
    {
        return $this->reservePurchasingSubjectHotel->updateOrCreate($judgeField, $data);
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
        foreach ($this->reservePurchasingSubjectHotel->where('reserve_schedule_id', $reserveScheduleId)->whereNotIn('id', $notDeleteIds)->get() as $row) {
            // 1行ずつ削除しないと、Modelのstatic::deleting が呼ばれないようなのでforeachで1行ずつ処理
            $res[] = $row->id; // idを記録
            if ($isSoftDelete) {
                $row->delete();
            } else {
                $row->forceDelete();
            }
        }
        return $res;

    }
}
