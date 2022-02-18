<?php
namespace App\Repositories\ReservePurchasingSubject;

use App\Models\ReservePurchasingSubject;
use Illuminate\Support\Collection;

class ReservePurchasingSubjectRepository implements ReservePurchasingSubjectRepositoryInterface
{
    public function __construct(ReservePurchasingSubject $reservePurchasingSubject)
    {
        $this->reservePurchasingSubject = $reservePurchasingSubject;
    }

    public function create(array $data) : ReservePurchasingSubject
    {
        return $this->reservePurchasingSubject->create($data);
    }

    /**
     * 更新or新規
     */
    public function updateOrCreate(array $judgeField, array $data) : ReservePurchasingSubject
    {
        return $this->reservePurchasingSubject->updateOrCreate($judgeField, $data);
    }

    /**
     * スケジュールID紐づく科目における該当ID以外を削除
     *
     * @param int $reserveScheduleId スケジュールID
     * @param string $subjectableType 科目モデル名
     * @param array $notDeleteSubjectableIds 削除しない科目ID一覧
     * @param boolean $isSoftDelete 論理削除か否か
     */
    public function deleteOtherSubjectableIdsForSchedule(int $reserveScheduleId, string $subjectableType, array $notDeleteSubjectableIds, bool $isSoftDelete = true) : bool
    {
        foreach ($this->reservePurchasingSubject->where('reserve_schedule_id', $reserveScheduleId)->where('subjectable_type', $subjectableType)->whereNotIn('subjectable_id', $notDeleteSubjectableIds)->get() as $row) {
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
     * 当該日程IDに対して出金履歴がある場合はtrue
     * 
     * @param int $reserveScheduleId スケジュールID
     * @return bool
     */
    public function existWithdrawalHistoryByReserveScheduleId(int $reserveScheduleId) : bool
    {
        // subjectableポリモーフィックリレーションそれぞれに対して有効(enabled_reserve_participant_prices)な入金履歴があるか確認。ちょっとSQLが強引なので、リファクタリングできないか検討
        return $this->reservePurchasingSubject
            ->where('reserve_schedule_id', $reserveScheduleId)
            ->whereHasMorph('subjectable', [
                'App\Models\ReservePurchasingSubjectOption',
                'App\Models\ReservePurchasingSubjectAirplane',
                'App\Models\ReservePurchasingSubjectHotel'
            ], function ($q) {
                $q->whereHas('enabled_reserve_participant_prices.account_payable_detail.agency_withdrawals');
            })->exists();
    }
}
