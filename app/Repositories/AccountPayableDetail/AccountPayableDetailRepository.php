<?php
namespace App\Repositories\AccountPayableDetail;

use App\Models\AccountPayableDetail;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AccountPayableDetailRepository implements AccountPayableDetailRepositoryInterface
{
    /**
    * @param object $accountPayableDetail
    */
    public function __construct(AccountPayableDetail $accountPayableDetail)
    {
        $this->accountPayableDetail = $accountPayableDetail;
    }

    /**
     * 当該レコードを取得
     *
     * データがない場合は 404ステータス
     *
     * @param int $id
     * @param int $isLock 行ロックして取得する場合はtrue
     */
    public function find(int $id, array $with = [], array $select = [], bool $isLock = false): AccountPayableDetail
    {
        $query = $this->accountPayableDetail;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        return $isLock ? $query->lockForUpdate()->findOrFail($id) : $query->findOrFail($id);
    }

    /**
     * 検索して1件取得
     */
    public function findWhere(array $where, array $with=[], array $select=[]) : ?AccountPayableDetail
    {
        $query = $this->accountPayableDetail;
        
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
     * 当該条件のレコードが存在するか
     */
    public function whereExists($where) : ?AccountPayableDetail
    {
        $query = $this->accountPayableDetail;
        foreach ($where as $key => $val) {
            $query = $query->where($key, $val);
        }
        return $query->first();
    }

    /**
     * 保存
     */
    public function save($data) : AccountPayableDetail
    {
        return $this->accountPayableDetail->create($data);
        // $accountPayableDetail =$this->accountPayableDetail;
        // $accountPayableDetail->fill($data)->save();
        // return $accountPayableDetail;
    }

    public function update(int $id, array $data): AccountPayableDetail
    {
        $accountPayableDetail = $this->find($id);
        $accountPayableDetail->fill($data)->save();
        return $accountPayableDetail;
    }

    /**
     * フィールド更新
     */
    public function updateField(int $id, array $data): AccountPayableDetail
    {
        $this->accountPayableDetail->where('id', $id)->update($data);
        return $this->find($id);
        // $accountPayableDetail = $this->accountPayableDetail->findOrFail($id);
        // foreach ($data as $k => $v) {
        //     $accountPayableDetail->{$k} = $v; // プロパティに値をセット
        // }
        // $accountPayableDetail->save();
        // return $this->find($id);
    }

    /**
     * 登録or更新
     */
    public function updateOrCreate(array $where, array $params) : AccountPayableDetail
    {
        return $this->accountPayableDetail->updateOrCreate($where, $params);
    }

    ///////////////// 以下は予約済ステータス専用処理。メソッド末尾が Reserved
    
    /**
     * ページネーション で取得
     *
     * @param ?string $applicationStep 申し込み段階。全レコード対象の場合はnull
     * @var $limit
     * @return object
     */
    public function paginateByAgencyId(int $agencyId, array $params, int $limit, bool $isValid = true, ?string $applicationStep, array $with, array $select) : LengthAwarePaginator
    {
        $query = $applicationStep === config('consts.reserves.APPLICATION_STEP_RESERVE') ? $this->accountPayableDetail->decided() : $this->accountPayableDetail; // スコープを設定
        
        // $query = $isValid ? $query->isValid() : $query; // valid=trueのスコープ

        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        
        foreach ($params as $key => $val) {
            if (is_empty($val)) {
                continue;
            }

            if (strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_PREFIX')) === 0) { // カスタム項目
                // 子テーブルとなるagency_withdrawalsレコードのカスタム項目が対象
                $query = $query->whereHas('agency_withdrawals.v_agency_withdrawal_custom_values', function ($q) use ($key, $val) {
                    $q->where('key', $key)->where('val', 'like', "%$val%");
                });
            } elseif ($key === 'reserve_number') { // 予約番号
                $query = $query->whereHas('reserve', function ($q) use ($val) {
                    $q->where('control_number', 'like', "%$val%");
                });
            } elseif ($key === 'payable_number') { // 買い掛け金番号
                $query = $query->whereHas('account_payable', function ($q) use ($key, $val) {
                    $q->where($key, $val);
                });
            } elseif ($key === 'last_manager_id' || $key === 'status') { // 自社担当、ステータス
                $query = $query->where($key, $val);
            } elseif ($key=== 'payment_date_from') { // 支払予定日(From)
                $query = $query->where('payment_date', '>=', $val);
            } elseif ($key=== 'payment_date_to') { // 支払予定日(To)
                $query = $query->where('payment_date', '<=', $val);
            } else { // 仕入先
                $query = $query->where($key, 'like', "%$val%");
            }
        }

        return $query->where('account_payable_details.agency_id', $agencyId)->sortable()->paginate($limit); // sortableする際にagency_idがリレーション先のテーブルにも存在するのでエラー回避のために明示的にagency_idを指定する
    }

    // /**
    //  * 当該予約に対するaccount_payment_detailsを取得
    //  * paginateByAgencyIdメソッドを予約番号で検索した場合の結果とほぼ同じ
    //  * 
    //  * @param ?string $applicationStep 申し込み段階。全レコード対象の場合はnull
    //  */
    // public function getByReserveNumber(string $reserveNumber, int $agencyId, ?string $applicationStep = null, array $with = [], array $select=[]) : Collection
    // {
    //     $query = $applicationStep === config('consts.reserves.APPLICATION_STEP_RESERVE') ? $this->accountPayableDetail->decided() : $this->accountPayableDetail; // スコープを設定
        
    //     $query = $with ? $query->with($with) : $query;
    //     $query = $select ? $query->select($select) : $query;

    //     $query = $query->whereHas('reserve', function ($q) use ($reserveNumber) {
    //         $q->where('control_number', $reserveNumber);
    //     });

    //     return $query->where('account_payable_details.agency_id', $agencyId)->sortable()->get(); // sortableする際にagency_idがリレーション先のテーブルにも存在するのでエラー回避のために明示的にagency_idを指定する
    // }

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
            $this->accountPayableDetail->destroy($id);
        } else {
            $this->find($id)->forceDelete();
        }
        return true;
    }
}
