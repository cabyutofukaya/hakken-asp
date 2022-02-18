<?php
namespace App\Repositories\ReserveParticipantAirplanePrice;

use App\Models\ReserveParticipantAirplanePrice;
use Illuminate\Support\Collection;

class ReserveParticipantAirplanePriceRepository implements ReserveParticipantAirplanePriceRepositoryInterface
{
    public function __construct(ReserveParticipantAirplanePrice $reserveParticipantAirplanePrice)
    {
        $this->reserveParticipantAirplanePrice = $reserveParticipantAirplanePrice;
    }

    /**
     * 検索して全件取得
     */
    public function getWhere(array $where, array $with=[], array $select=[]) : Collection
    {
        $query = $this->reserveParticipantAirplanePrice;
        
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;

        foreach ($where as $key => $val) {
            $query = $query->where($key, $val);
        }
        return $query->get();
    }

    /**
     * 当該参加者IDのカラムを更新
     */
    public function updateByParticipantId(int $participantId, bool $valid): bool
    {
        foreach ($this->reserveParticipantAirplanePrice->where('participant_id', $participantId)->get() as $row) {
            $row->valid = $valid;
            $row->save();
        }
        return true;
    }

    /**
     * 当該参加者IDに紐づくレコードを削除
     */
    public function deleteByParticipantId(int $participantId, bool $ifExistWithdrawalDelete = false, bool $isSoftDelete=true): bool
    {
        foreach ($this->reserveParticipantAirplanePrice->with(['account_payable_detail.agency_withdrawals'])->where('participant_id', $participantId)->get() as $row) {
            if (!$ifExistWithdrawalDelete && $row->account_payable_detail && !$row->account_payable_detail->agency_withdrawals->isEmpty()) { // $ifExistWithdrawalDelete=false。出金登録がある場合は削除しない
                continue;
            }
            $isSoftDelete ? $row->delete() : $row->forceDelete();
        }
        return true;
    }

    /**
     * 当該reserve_purchasing_subject_airplane_idに紐づく出金履歴が存在する場合はtrue
     *
     * @param int $reservePurchasingSubjectAirplaneId
     * @return bool
     */
    public function existWithdrawalHistoryByReservePurchasingSubjectAirplaneId(int $reservePurchasingSubjectAirplaneId) : bool
    {
        // 有効ステータスのみ対象
        return
            $this->reserveParticipantAirplanePrice->isValid() // 有効のみチェック対象に
            ->where('reserve_purchasing_subject_airplane_id', $reservePurchasingSubjectAirplaneId)
            ->whereHas("account_payable_detail.agency_withdrawals")
            ->exists();
    }
}
