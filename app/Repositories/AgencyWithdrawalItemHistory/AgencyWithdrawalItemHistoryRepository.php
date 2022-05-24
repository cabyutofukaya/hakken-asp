<?php
namespace App\Repositories\AgencyWithdrawalItemHistory;

use App\Models\AgencyWithdrawalItemHistory;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AgencyWithdrawalItemHistoryRepository implements AgencyWithdrawalItemHistoryRepositoryInterface
{
    /**
    * @param object $agencyWithdrawalItemHistory
    */
    public function __construct(AgencyWithdrawalItemHistory $agencyWithdrawalItemHistory)
    {
        $this->agencyWithdrawalItemHistory = $agencyWithdrawalItemHistory;
    }

    /**
     * 当該レコードを取得
     *
     * データがない場合は 404ステータス
     *
     * @param int $id
     */
    public function find(int $id, array $with = [], array $select = []): ?AgencyWithdrawalItemHistory
    {
        $query = $this->agencyWithdrawalItemHistory;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        return $query->find($id);
    }

    /**
     * 当該予約の出金額合計を取得
     * 行ロックで取得
     *
     * @param int $reserveId 予約ID
     * @return int
     */
    public function getSumAmountByReserveId(int $reserveId, bool $isLock=false) : int
    {
        return $isLock ? $this->agencyWithdrawalItemHistory->where('reserve_id', $reserveId)->lockForUpdate()->sum("amount") :  $this->agencyWithdrawalItemHistory->where('reserve_id', $reserveId)->sum("amount");
    }

    // /**
    //  * 当該支払い明細の出金額合計を取得
    //  * 行ロックで取得
    //  *
    //  * @param int $accountPayableDetailId 支払い明細ID
    //  * @return int
    //  */
    // public function getSumAmountByAccountPayableDetailId(int $accountPayableDetailId, bool $isLock=false) : int
    // {
    //     return $isLock ? $this->agencyWithdrawalItemHistory->where('account_payable_detail_id', $accountPayableDetailId)->lockForUpdate()->sum("amount") :  $this->agencyWithdrawalItemHistory->where('account_payable_detail_id', $accountPayableDetailId)->sum("amount");
    // }

    /**
     * 出金登録
     */
    public function create(array $data): AgencyWithdrawalItemHistory
    {
        return $this->agencyWithdrawalItemHistory->create($data);
    }

    /**
     * 検索して全件取得
     */
    public function getWhere(array $where, array $with=[], array $select=[]) : Collection
    {
        $query = $this->agencyWithdrawalItemHistory;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;

        foreach ($where as $key => $val) {
            if (is_empty($val)) {
                continue;
            }
            $query = $query->where($key, $val);
        }
        return $query->get();
    }

    /**
     * 検索して1件取得
     */
    public function findWhere(array $where, array $with=[], array $select=[]) : ?AgencyWithdrawalItemHistory
    {
        $query = $this->agencyWithdrawalItemHistory;
        
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;

        foreach ($where as $key => $val) {
            if (is_empty($val)) {
                continue;
            }
            $query = $query->where($key, $val);
        }
        return $query->first();
    }

    /**
     * 当該予約IDに紐づく出金情報があるか否か
     */
    public function isExistsParticipant(int $participantId, int $reserveId) : bool
    {
        return $this->agencyWithdrawalItemHistory->where('participant_id', $participantId)->where('reserve_id', $reserveId)->exists();
    }

    /**
     * 削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue
     * @return boolean
     */
    public function delete(int $id, bool $isSoftDelete): bool
    {
        if ($isSoftDelete) {
            $this->agencyWithdrawalItemHistory->destroy($id);
        } else {
            $this->find($id)->forceDelete();
        }
        return true;
    }

    /**
     * 条件検索で削除
     *
     * @param array $where
     * @param boolean $isSoftDelete 論理削除の場合はtrue
     * @return boolean
     */
    public function deleteWhere(array $where, bool $isSoftDelete): bool
    {
        $query = $this->agencyWithdrawalItemHistory;
        
        foreach ($where as $key => $val) {
            if (is_empty($val)) {
                continue;
            }
            $query = $query->where($key, $val);
        }

        if ($isSoftDelete) {
            $query->delete();
        } else {
            $query->forceDelete();
        }
        return true;
    }
}
