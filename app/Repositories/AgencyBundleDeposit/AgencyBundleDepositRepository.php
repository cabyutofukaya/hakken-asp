<?php
namespace App\Repositories\AgencyBundleDeposit;

use App\Models\AgencyBundleDeposit;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AgencyBundleDepositRepository implements AgencyBundleDepositRepositoryInterface
{
    /**
    * @param object $agencyBundleDeposit
    */
    public function __construct(AgencyBundleDeposit $agencyBundleDeposit)
    {
        $this->agencyBundleDeposit = $agencyBundleDeposit;
    }

    /**
     * 当該レコードを取得
     *
     * データがない場合は 404ステータス
     *
     * @param int $id
     */
    public function find(int $id, array $with = [], array $select = [], bool $getDeleted = false): AgencyBundleDeposit
    {
        $query = $this->agencyBundleDeposit;
        $query = $getDeleted ? $query->withTrashed() : $query;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        return $query->findOrFail($id);
    }

    /**
     * 検索して1件取得
     */
    public function findWhere(array $where, array $with=[], array $select=[]) : ?AgencyBundleDeposit
    {
        $query = $this->agencyBundleDeposit;
        
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

    // /**
    //  * 当該支払い明細の出金額合計を取得
    //  * 行ロックで取得
    //  *
    //  * @param int $accountPayableDetailId 支払い明細ID
    //  * @return int
    //  */
    // public function getSumAmountByAccountPayableDetailId(int $accountPayableDetailId, bool $isLock=false) : int
    // {
    //     return $isLock ? $this->agencyBundleDeposit->where('account_payable_detail_id', $accountPayableDetailId)->lockForUpdate()->sum("amount") :  $this->agencyBundleDeposit->where('account_payable_detail_id', $accountPayableDetailId)->sum("amount");
    // }

    /**
     * 入金登録
     */
    public function create(array $data): AgencyBundleDeposit
    {
        return $this->agencyBundleDeposit->create($data);
        // $agencyBundleDeposit = $this->agencyBundleDeposit;
        // $agencyBundleDeposit->fill($data)->save();
        // return $agencyBundleDeposit;
    }

    /**
     * 削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue
     * @return boolean
     */
    public function delete(int $id, bool $isSoftDelete = true): bool
    {
        if ($isSoftDelete) {
            $this->agencyBundleDeposit->destroy($id);
        } else {
            $this->find($id)->forceDelete();
        }
        return true;
    }

    /**
     * 入金識別IDを指定して削除
     */
    public function deleteByIdentifierId(string $identifierId, bool $isSoftDelete = true): bool
    {
        if (($agencyBundleDeposit = $this->findWhere(['identifier_id' => $identifierId]))) {
            if ($isSoftDelete) {
                $agencyBundleDeposit->delete();
            } else {
                $agencyBundleDeposit->forceDelete();
            }
        }
        return true;
    }
}
