<?php
namespace App\Repositories\AgencyWithdrawal;

use App\Models\AgencyWithdrawal;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AgencyWithdrawalRepository implements AgencyWithdrawalRepositoryInterface
{
    /**
    * @param object $agencyWithdrawal
    */
    public function __construct(AgencyWithdrawal $agencyWithdrawal)
    {
        $this->agencyWithdrawal = $agencyWithdrawal;
    }

    /**
     * 当該レコードを取得
     *
     * データがない場合は 404ステータス
     *
     * @param int $id
     */
    public function find(int $id, array $with = [], array $select = []): AgencyWithdrawal
    {
        $query = $this->agencyWithdrawal;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        return $query->findOrFail($id);
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
        return $isLock ? $this->agencyWithdrawal->where('reserve_id', $reserveId)->lockForUpdate()->sum("amount") :  $this->agencyWithdrawal->where('reserve_id', $reserveId)->sum("amount");
    }

    /**
     * 当該支払い明細の出金額合計を取得
     * 行ロックで取得
     *
     * @param int $accountPayableDetailId 支払い明細ID
     * @return int
     */
    public function getSumAmountByAccountPayableDetailId(int $accountPayableDetailId, bool $isLock=false) : int
    {
        return $isLock ? $this->agencyWithdrawal->where('account_payable_detail_id', $accountPayableDetailId)->lockForUpdate()->sum("amount") :  $this->agencyWithdrawal->where('account_payable_detail_id', $accountPayableDetailId)->sum("amount");
    }

    /**
     * 出金登録
     */
    public function create(array $data): AgencyWithdrawal
    {
        return $this->agencyWithdrawal->create($data);
    }

    /**
     * バルクインサート
     */
    public function insert(array $rows) : bool
    {
        $this->agencyWithdrawal->insert($rows);
        return true;
    }

    /**
     * 検索して全件取得
     */
    public function getWhere(array $where, array $with=[], array $select=[]) : Collection
    {
        $query = $this->agencyWithdrawal;
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
     * 当該予約IDに紐づく出金情報があるか否か
     */
    public function isExistsParticipant(int $participantId, int $reserveId) : bool
    {
        return $this->agencyWithdrawal->where('participant_id', $participantId)->where('reserve_id', $reserveId)->exists();
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
            $this->agencyWithdrawal->destroy($id);
        } else {
            $this->find($id)->forceDelete();
        }
        return true;
    }
}
