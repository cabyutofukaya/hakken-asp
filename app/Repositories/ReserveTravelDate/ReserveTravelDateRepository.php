<?php
namespace App\Repositories\ReserveTravelDate;

use App\Models\ReserveTravelDate;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ReserveTravelDateRepository implements ReserveTravelDateRepositoryInterface
{
    /**
    * @param object $reserveTravelDate
    */
    public function __construct(ReserveTravelDate $reserveTravelDate)
    {
        $this->reserveTravelDate = $reserveTravelDate;
    }

    /**
     * 条件で取得
     */
    public function getWhere(array $where, array $select = [], bool $getDeleted = false): Collection
    {
        $query = $this->reserveTravelDate;
        $query = $select ? $query->select($select) : $query;

        foreach ($where as $key => $val) {
            $query = $query->where($key, $val);
        }
        
        $query = $getDeleted ? $query->withTrashed() : $query;
        return $query->get();
    }

    /**
     * 行程IDにおける該当ID以外を削除
     *
     * @param int $reserveItineraryId 行程ID
     * @param array $notDeleteIds 削除しないID一覧
     * @param boolean $isSoftDelete 論理削除か否か
     */
    public function deleteOtherIdsForReserveItinerary(int $reserveItineraryId, array $notDeleteIds, bool $isSoftDelete = true) : bool
    {
        foreach ($this->reserveTravelDate->with(['reserve_schedules'])->where('reserve_itinerary_id', $reserveItineraryId)->whereNotIn('id', $notDeleteIds)->get() as $row) {
            // 1行ずつ削除しないと、Modelのstatic::deleting が呼ばれないようなのでforeachで1行ずつ処理
            if ($isSoftDelete) {
                $row->delete();
            } else {
                $row->forceDelete();
            }
        }
        return true;
    }

    /**
     * 行程日レコードを削除
     */
    public function deleteForItineraryDays(int $reserveItineraryId, array $deleteDays, bool $isSoftDelete = true) : bool
    {
        foreach ($this->reserveTravelDate->with([
            'reserve_schedules.reserve_purchasing_subjects.subjectable.reserve_participant_prices.account_payable_detail.agency_withdrawals'
            ])->where('reserve_itinerary_id', $reserveItineraryId)->whereIn('travel_date', $deleteDays)->get() as $row) {
            // 1行ずつ削除しないと、Modelのstatic::deleting が呼ばれないようなのでforeachで1行ずつ処理
            if ($isSoftDelete) { //論理削除
                $row->delete();
            } else { // 物理削除
                $row->forceDelete();
            }
        }
        return true;
    }
}
