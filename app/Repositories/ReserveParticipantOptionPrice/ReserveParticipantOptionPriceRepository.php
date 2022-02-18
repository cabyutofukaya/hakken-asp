<?php
namespace App\Repositories\ReserveParticipantOptionPrice;

use App\Models\ReserveParticipantOptionPrice;
use Illuminate\Support\Collection;

class ReserveParticipantOptionPriceRepository implements ReserveParticipantOptionPriceRepositoryInterface
{
    public function __construct(ReserveParticipantOptionPrice $reserveParticipantOptionPrice)
    {
        $this->reserveParticipantOptionPrice = $reserveParticipantOptionPrice;
    }

    /**
     * 当該参加者IDのカラムを更新
     */
    public function updateByParticipantId(int $participantId, bool $valid): bool
    {
        foreach ($this->reserveParticipantOptionPrice->where('participant_id', $participantId)->get() as $row) {
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
        foreach ($this->reserveParticipantOptionPrice->with(['account_payable_detail.agency_withdrawals'])->where('participant_id', $participantId)->get() as $row) {
            if (!$ifExistWithdrawalDelete && $row->account_payable_detail && !$row->account_payable_detail->agency_withdrawals->isEmpty()) { // $ifExistWithdrawalDelete=false。出金登録がある場合は削除しない
                continue;
            }
            $isSoftDelete ? $row->delete() : $row->forceDelete();
        }
        return true;
    }

    /**
     * 当該reserve_purchasing_subject_option_idに紐づく出金履歴が存在する場合はtrue
     *
     * @param int $reservePurchasingSubjectOptionId
     * @return bool
     */
    public function existWithdrawalHistoryByReservePurchasingSubjectOptionId(int $reservePurchasingSubjectOptionId) : bool
    {
        // 有効ステータスのみ対象
        return
            $this->reserveParticipantOptionPrice->isValid() // 有効のみチェック対象に
            ->where('reserve_purchasing_subject_option_id', $reservePurchasingSubjectOptionId)
            ->whereHas("account_payable_detail.agency_withdrawals")
            ->exists();
    }
}
