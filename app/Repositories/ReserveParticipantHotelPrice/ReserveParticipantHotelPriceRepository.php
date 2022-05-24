<?php
namespace App\Repositories\ReserveParticipantHotelPrice;

use App\Models\ReserveParticipantHotelPrice;
use Illuminate\Support\Collection;

class ReserveParticipantHotelPriceRepository implements ReserveParticipantHotelPriceRepositoryInterface
{
    public function __construct(ReserveParticipantHotelPrice $reserveParticipantHotelPrice)
    {
        $this->reserveParticipantHotelPrice = $reserveParticipantHotelPrice;
    }

    /**
     * 検索して全件取得
     */
    public function getWhere(array $where, array $with=[], array $select=[], bool $getDeleted = false) : Collection
    {
        $query = $this->reserveParticipantHotelPrice;
        $query = $getDeleted ? $query->withTrashed() : $query;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;

        foreach ($where as $key => $val) {
            $query = $query->where($key, $val);
        }

        // return $query->get();
        // ↓一気に取得すると危険なので以下のようにした方が良いかも
        $res = [];
        $query->chunk(300, function ($rows) use (&$res) {
            foreach ($rows as $row) {
                $res[] = $row;
            }
        });

        return collect($res);
    }

    /**
     * 当該参加者IDのカラムを更新
     */
    public function updateByParticipantId(int $participantId, bool $valid): bool
    {
        $this->reserveParticipantHotelPrice->where('participant_id', $participantId)->update(['valid' => $valid]);
        return true;

        // foreach ($this->reserveParticipantHotelPrice->where('participant_id', $participantId)->get() as $row) {
        //     $row->valid = $valid;
        //     $row->save();
        // }
        // return true;
    }

        
    /**
     * バルクインサート
     */
    public function insert(array $params) : bool
    {
        $this->reserveParticipantHotelPrice->insert($params);
        return true;
    }

    /**
     * バルクアップデート
     *
     * @param array $params
     */
    public function updateBulk(array $params, string $id) : bool
    {
        $this->reserveParticipantHotelPrice->updateBulk($params, $id);
        return true;
    }

    /**
     * 当該参加者IDに紐づくレコードを削除
     */
    public function deleteByParticipantId(int $participantId, bool $ifExistWithdrawalDelete = false, bool $isSoftDelete=true): bool
    {
        foreach ($this->reserveParticipantHotelPrice->with(['account_payable_detail.agency_withdrawals'])->where('participant_id', $participantId)->get() as $row) {
            if (!$ifExistWithdrawalDelete && $row->account_payable_detail && !$row->account_payable_detail->agency_withdrawals->isEmpty()) { // $ifExistWithdrawalDelete=false。出金登録がある場合は削除しない
                continue;
            }
            $isSoftDelete ? $row->delete() : $row->forceDelete();
        }
        return true;
    }

    /**
     * 当該reserve_purchasing_subject_hotel_idに紐づく出金履歴が存在する場合はtrue
     *
     * @param int $reservePurchasingSubjectHotelId
     * @return bool
     */
    public function existWithdrawalHistoryByReservePurchasingSubjectHotelId(int $reservePurchasingSubjectHotelId) : bool
    {
        // 有効無効全ステータスが対象
        return
            $this->reserveParticipantHotelPrice
                ->where('reserve_purchasing_subject_hotel_id', $reservePurchasingSubjectHotelId)
                ->whereHas("account_payable_detail.agency_withdrawals")
                ->exists();
    }

    /**
     * 当該reserve_purchasing_subject_hotel_idに紐づくキャンセルレコードが存在する場合はtrue
     *
     * @param int $reservePurchasingSubjectHotelId
     * @return bool
     */
    public function existCancelByReservePurchasingSubjectHotelId(int $reservePurchasingSubjectHotelId) : bool
    {
        // 有効無効全ステータスが対象
        return
            $this->reserveParticipantHotelPrice
                ->where('reserve_purchasing_subject_hotel_id', $reservePurchasingSubjectHotelId)
                ->where('is_alive_cancel', true) // 明示的なキャンセル
                ->exists();
    }

    /**
     * IDリストのレコードを更新
     * 
     * @param array $update
     * @param array $ids
     * @return boolean
     */
    public function updateIds(array $update, array $ids) : bool
    {
        // foreach ($this->reserveParticipantHotelPrice->whereIn('id', $ids)->get() as $row) {
        //     foreach ($update as $key => $val) {
        //         $row->{$key} = $val;
        //     }
        //     $row->save();
        // }
        // return true;

        $this->reserveParticipantHotelPrice->whereIn('id', $ids)->update($update);
        return true;
    }

    /**
     * 条件にマッチするレコードが存在するか否か
     * 
     * @param array $where
     * @param bool $getDeleted
     * @return bool
     */
    public function whereExists(array $where, bool $getDeleted = false) : bool
    {
        $query = $this->reserveParticipantHotelPrice;
        $query = $getDeleted ? $query->withTrashed() : $query;
        foreach ($where as $key => $val) {
            $query = $query->where($key, $val);
        }
        return $query->exists();
    }

    /**
     * 条件にマッチするレコードを更新
     *
     * @param array $update
     * @param array $ids
     * @return boolean
     */
    public function updateWhere(array $update, array $where) : bool
    {
        $query = $this->reserveParticipantHotelPrice;
        foreach ($where as $key => $val) {
            $query = $query->where($key, $val);
        }
        $query->update($update);
        return true;

        // $query = $this->reserveParticipantHotelPrice;
        // foreach ($where as $key => $val) {
        //     $query = $query->where($key, $val);
        // }
        // foreach ($query->get() as $row) {
        //     foreach ($update as $key => $val) {
        //         $row->{$key} = $val;
        //     }
        //     $row->save();
        // }
        // return true;
    }
}
