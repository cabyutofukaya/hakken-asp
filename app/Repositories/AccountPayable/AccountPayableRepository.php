<?php
namespace App\Repositories\AccountPayable;

use App\Models\AccountPayable;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AccountPayableRepository implements AccountPayableRepositoryInterface
{
    /**
    * @param object $accountPayable
    */
    public function __construct(AccountPayable $accountPayable)
    {
        $this->accountPayable = $accountPayable;
    }

    /**
     * 全件取得
     */
    public function getByReserveItineraryId(int $reserveItineraryId, array $with=[], array $select=[], bool $getDeleted = false) : Collection
    {
        $query = $this->accountPayable;
        $query = $getDeleted ? $query->withTrashed() : $query;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        return $query->where('reserve_itinerary_id', $reserveItineraryId)->sortable()->get();
    }

    public function save($data) : AccountPayable
    {
        return $this->accountPayable->create($data);
        // $accountPayable = $this->accountPayable;
        // $accountPayable->fill($data)->save();
        // return $accountPayable;
    }

    /**
     * 登録or更新
     */
    public function updateOrCreate(array $where, array $params) : AccountPayable
    {
        return $this->accountPayable->updateOrCreate($where, $params);
    }

    /**
     * 当該条件のレコードが存在するか
     */
    public function whereExists($where) : ?AccountPayable
    {
        $query = $this->accountPayable;
        foreach ($where as $key => $val) {
            $query = $query->where($key, $val);
        }
        return $query->first();
    }

    // /**
    //  * 当該行程管理IDに紐づく買掛金レコードで詳細レコードを持たないものを削除
    //  *
    //  * @param bool $isSoftDelete 論理削除の場合はTrue
    //  */
    // public function deleteNoDetailByReserveItineraryId(int $reserveItineraryId, bool $isSoftDelete = true) : bool
    // {
    //     foreach ($this->accountPayable->where('reserve_itinerary_id', $reserveItineraryId)->doesntHave('account_payable_details')->get() as $accountPayable) {
    //         if ($isSoftDelete) {
    //             $accountPayable->delete();
    //         } else {
    //             $accountPayable->forceDelete();
    //         }
    //     }
    //     return true;
    // }

    /**
     * 当該行程における、買掛金明細がなくなった買掛金レコードを削除（親レコード、子レコードとも）
     *
     * @param int $reserveItineraryId 行程ID
     * @param array $supplierIds 仕入先ID一覧。当リストに含まれる仕入先は削除対象外
     * @param bool $isSoftDelete 論理削除の場合はTrue
     */
    public function deleteLostPurchaseData(int $reserveItineraryId, array $supplierIds, bool $isSoftDelete = true) : bool
    {
        // 出金登録レコードを持たない支払い詳細レコードを削除
        foreach ($this->accountPayable->with('account_payable_details.agency_withdrawals')->where('reserve_itinerary_id', $reserveItineraryId)->whereNotIn('supplier_id', $supplierIds)->get() as $accountPayable) {
            foreach ($accountPayable->account_payable_details()->doesntHave('agency_withdrawals')->get() as $accountPayableDetail) {
                if ($isSoftDelete) {
                    $accountPayableDetail->delete();
                } else {
                    $accountPayableDetail->forceDelete();
                }
            }
        }

        // 支払い詳細レコードを持たないaccount_payablesレコードを削除
        foreach ($this->accountPayable->where('reserve_itinerary_id', $reserveItineraryId)->doesntHave('account_payable_details')->get() as $accountPayable) {
            if ($isSoftDelete) {
                $accountPayable->delete();
            } else {
                $accountPayable->forceDelete();
            }
        }
        return true;
    }

    /**
     * 当該予約において、買い掛け金明細がなくなったaccount_payablesレコードを削除
     */
    public function deleteDoseNotHaveDetails(int $reserveId, bool $isSoftDelete = true) : bool
    {
        foreach ($this->accountPayable->where('reserve_id', $reserveId)->doesntHave('account_payable_details')->get() as $accountPayable) {
            if ($isSoftDelete) {
                $accountPayable->delete();
            } else {
                $accountPayable->forceDelete();
            }
        }
        return true;
    }
}
