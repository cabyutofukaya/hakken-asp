<?php
namespace App\Repositories\AccountPayableDetail;

use App\Models\AccountPayableDetail;
use App\Models\ReserveParticipantOptionPrice;
use App\Models\ReserveParticipantAirplanePrice;
use App\Models\ReserveParticipantHotelPrice;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class AccountPayableDetailRepository implements AccountPayableDetailRepositoryInterface
{
    /**
    * @param object $accountPayableDetail
    */
    public function __construct(AccountPayableDetail $accountPayableDetail, ReserveParticipantOptionPrice $reserveParticipantOptionPrice, ReserveParticipantAirplanePrice $reserveParticipantAirplanePrice, ReserveParticipantHotelPrice $reserveParticipantHotelPrice)
    {
        $this->accountPayableDetail = $accountPayableDetail;
        $this->reserveParticipantOptionPrice = $reserveParticipantOptionPrice;
        $this->reserveParticipantAirplanePrice = $reserveParticipantAirplanePrice;
        $this->reserveParticipantHotelPrice = $reserveParticipantHotelPrice;
    }

    /**
     * 当該レコードを取得
     *
     * データがない場合は 404ステータス
     *
     * @param int $id
     * @param int $isLock 行ロックして取得する場合はtrue
     */
    public function find(int $id, array $with = [], array $select = [], bool $isLock = false): ?AccountPayableDetail
    {
        $query = $this->accountPayableDetail;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        return $isLock ? $query->lockForUpdate()->find($id) : $query->find($id);
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
     * 検索して全件取得
     */
    public function getWhere(array $where, array $with=[], array $select=[]) : Collection
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
     * 当該仕入IDリストに紐づく一覧を取得
     *
     * @param string $saleableType 仕入科目
     * @param array $saleableIds 仕入科目ID一覧
     */
    public function getBySaleableIds(string $saleableType, array $saleableIds, array $select=['id']) : Collection
    {
        return $this->accountPayableDetail->select($select)->where('saleable_type', $saleableType)->whereIn('saleable_id', $saleableIds)->get();
    }

    /**
     * 当該条件のレコードが存在するか
     */
    public function whereExists($where) : bool
    {
        $query = $this->accountPayableDetail;
        foreach ($where as $key => $val) {
            $query = $query->where($key, $val);
        }
        return $query->exists();
    }

    /**
     * 仕入先＆商品毎にまとめるための条件クエリを取得
     */
    public function getSummarizeItemQuery(array $where)
    {
        $query = $this->accountPayableDetail;
        foreach ($where as $key => $val) {
            $query = $query->where($key, $val);
        }
        return $query;
    }

    /**
     * 保存
     */
    public function save($data) : AccountPayableDetail
    {
        return $this->accountPayableDetail->create($data);
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
    }

    /**
     * 登録or更新
     */
    public function updateOrCreate(array $where, array $params) : AccountPayableDetail
    {
        return $this->accountPayableDetail->updateOrCreate($where, $params);
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
        $query = $this->accountPayableDetail;
        foreach ($where as $key => $val) {
            $query = $query->where($key, $val);
        }
        foreach ($query->get() as $row) {
            foreach ($update as $key => $val) {
                $row->{$key} = $val;
            }
            $row->save();
        }
        return true;
    }

    /**
     * 当該saleble_typeに関連するレコードをバルクアップデート
     */
    public function updateWhereBulk(array $where, array $params, string $id='id') : bool
    {
        $query = $this->accountPayableDetail;
        foreach ($where as $key => $val) {
            $query = $query->where($key, $val);
        }
        $query->updateBulk($params, $id);
        return true;
    }

    /**
     * バルクインサート
     */
    public function insert(array $params) : bool
    {
        $this->accountPayableDetail->insert($params);
        return true;
    }
    
    /**
     * バルクアップデート
     *
     * @param array $params
     */
    public function updateBulk(array $params, string $id) : bool
    {
        $this->accountPayableDetail->updateBulk($params, $id);
        return true;
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
    public function paginateByAgencyId(int $agencyId, string $subject, array $params, int $limit, ?string $applicationStep, array $with, array $select, bool $exZero = true) : LengthAwarePaginator
    {
        $query = $applicationStep === config('consts.reserves.APPLICATION_STEP_RESERVE') ? $this->accountPayableDetail->decided() : $this->accountPayableDetail; // スコープを設定

        if ($exZero) { // 請求額が0円だと、出金履歴が有っても非表示になるので注意。具合悪いようならこのフラグはなくす
            $query = $query->excludingzero();
        }

        // $query = $isValid ? $query->isValid() : $query; // valid=trueのスコープ

        // saleable対象モデルは無駄なSQLを発行させないように下記変換テーブルにて科目に対応したモデルに限定
        $saleableTbl = [
            config('consts.subject_categories.SUBJECT_CATEGORY_OPTION') => 'App\Models\ReserveParticipantOptionPrice',
            config('consts.subject_categories.SUBJECT_CATEGORY_AIRPLANE') => 'App\Models\ReserveParticipantAirplanePrice',
            config('consts.subject_categories.SUBJECT_CATEGORY_HOTEL') => 'App\Models\ReserveParticipantHotelPrice',
        ];

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
            // 「予約ID」「行程ID」「仕入先ID」「科目」「商品ID」は必須パラメータにつき曖昧検索はしない
            } elseif (in_array($key, ["reserve_id", "reserve_itinerary_id", "supplier_id", "subject", "item_id"], true)) {
                $query = $query->where($key, $val);
            } elseif ($key === 'participant_name') { // 参加者名(saleable.participant)
                // 科目に対応したポリモーフィックリレーションの参加者で検索
                $query = $query->whereHasMorph('saleable', [Arr::get($saleableTbl, $subject)], function ($q1) use ($val) {
                    $q1->whereHas('participant', function ($q2) use ($val) {
                        $q2->where('name', 'like', "%$val%");
                    });
                });
            } elseif ($key === 'last_manager_id' || $key === 'status') { // 自社担当、ステータス
                $query = $query->where($key, $val);
            } elseif ($key=== 'use_date_from') { // 利用日(From)
                $query = $query->where('use_date', '>=', $val);
            } elseif ($key=== 'use_date_to') { // 利用日(To)
                $query = $query->where('use_date', '<=', $val);
            } else { // 商品名
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
