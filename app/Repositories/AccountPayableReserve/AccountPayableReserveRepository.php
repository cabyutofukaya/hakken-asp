<?php
namespace App\Repositories\AccountPayableReserve;

use App\Models\AccountPayableReserve;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AccountPayableReserveRepository implements AccountPayableReserveRepositoryInterface
{
    /**
    * @param object $accountPayableReserve
    */
    public function __construct(AccountPayableReserve $accountPayableReserve)
    {
        $this->accountPayableReserve = $accountPayableReserve;
    }

    /**
     * 当該予約レコードを一件取得
     *
     * @param int $reserveId ID
     * @param array $select 取得カラム
     * @param bool $isLock 行ロックして取得する場合はtrue
     */
    public function findByReserveId(int $reserveId, array $with = [], array $select=[], bool $isLock = false) : ?AccountPayableReserve
    {
        $query = $this->accountPayableReserve;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        $query = $query->where('reserve_id', $reserveId);
        return $isLock ? $query->lockForUpdate()->first() : $query->first();
    }

    /**
     * 登録or更新
     */
    public function updateOrCreate(array $where, array $params) : AccountPayableReserve
    {
        return $this->accountPayableReserve->updateOrCreate($where, $params);
    }

    public function create(array $data) : AccountPayableReserve
    {
        return $this->accountPayableReserve->create($data);
    }

    public function update(int $id, array $data): AccountPayableReserve
    {
        $accountPayableReserve = $this->accountPayableReserve->find($id);
        $accountPayableReserve->fill($data)->save();
        return $accountPayableReserve;
    }

    /**
     * フィールド更新
     */
    public function updateField(int $id, array $data): AccountPayableReserve
    {
        $this->accountPayableReserve->where('id', $id)->update($data);
        return $this->accountPayableReserve->find($id);
    }

    ///////////////// 以下は予約済ステータス専用処理。メソッド末尾が Reserved
    
    /**
     * ページネーション で取得
     *
     * @param ?string $applicationStep 申し込み段階。全レコード対象の場合はnull
     * @var $limit
     * @param bool $exZero 仕入額・未払い額が0円のレコードを取得しない場合はtrue
     * @return object
     */
    public function paginateByAgencyId(int $agencyId, array $params, int $limit, ?string $applicationStep, array $with, array $select, bool $exZero = true) : LengthAwarePaginator
    {
        $query = $applicationStep === config('consts.reserves.APPLICATION_STEP_RESERVE') ? $this->accountPayableReserve->decided() : $this->accountPayableReserve; // スコープを設定
        
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        
        foreach ($params as $key => $val) {
            if (is_empty($val)) {
                continue;
            }

            if ($key === 'reserve_number') { // 予約番号
                $query = $query->whereHas('reserve', function ($q) use ($val) {
                    $q->where('control_number', 'like', "%$val%");
                });

            } elseif ($key=== 'manager_id') { // 自社担当
                $query = $query->whereHas('reserve', function ($q) use ($val) {
                    $q->where('manager_id', $val);
                });

            } elseif ($key=== 'departure_date_from') { // 出発日(From)
                $query = $query->whereHas('reserve', function ($q) use ($val) {
                    $q->where('departure_date', '>=', $val);
                });

            } elseif ($key=== 'departure_date_to') { // 出発日(To)
                $query = $query->whereHas('reserve', function ($q) use ($val) {
                    $q->where('departure_date', '<=', $val);
                });

            } elseif ($key === 'status') { // ステータス
                $query = $query->where($key, $val);
            } else { // 上記以外
                $query = $query->where($key, 'like', "%$val%");
            }
        }

        if ($exZero) { // 請求額が0円だと出金履歴が有っても非表示になるので注意。具合悪いようならこのフラグはなくす
            $query = $query->where(function ($q) {
                $q->where('amount_billed', "<>", 0)
                    ->orWhere('unpaid_balance', "<>", 0);
            })->where('status', '<>', config('consts.account_payable_reserves.STATUS_NONE'));
        }

        return $query->where('account_payable_reserves.agency_id', $agencyId)->sortable()->paginate($limit); // sortableする際にagency_idがリレーション先のテーブルにも存在するのでエラー回避のために明示的にagency_idを指定する
    }
}
