<?php
namespace App\Repositories\WebReserve;

use App\Models\Reserve;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * reservesテーブルのweb予約データを専用に扱うリポジトリ
 */
class WebReserveRepository implements WebReserveRepositoryInterface
{
    /**
    * @param object $agency
    */
    public function __construct(Reserve $reserve)
    {
        // 本リポジトリはWeb受付レコードのみ対象
        $this->reserve = $reserve->web();
    }

    /**
     * 当該IDを一件取得
     */
    public function find(int $id, array $with = [], array $select=[], bool $getDeleted = false) : Reserve
    {
        $query = $this->reserve;
        $query = $select ? $query->select($select) : $query;
        $query = $with ? $query->with($with) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        return $query->findOrFail($id);
    }

    /**
     * 依頼番号から1件取得
     *
     */
    public function findByRequestNumber(string $requestNumber, int $agencyId, array $with = [], array $select = [], bool $getDeleted = false) : Reserve
    {
        // $query = $this->reserve->draft(); // スコープは予約確定前 → 予約確定後のページで本メソッドを実行するケースがあるので、予約確定後のデータにもアクセスできるようにdraftスコープは一旦外し
        
        $query = $this->reserve;
        $query = $select ? $query->select($select) : $query;
        $query = $with ? $query->with($with) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        return $query
                ->where('request_number', $requestNumber)
                ->where('agency_id', $agencyId)
                ->firstOrFail();
    }

    /**
     * 見積番号から1件取得
     *
     */
    public function findByEstimateNumber(string $estimateNumber, int $agencyId, array $with = [], array $select = [], bool $getDeleted = false) : Reserve
    {
        // $query = $this->reserve->draft(); // スコープは予約確定前 → 予約確定後のページで本メソッドを実行するケースがあるので、予約確定後のデータにもアクセスできるようにdraftスコープは一旦外し

        $query = $this->reserve;
        $query = $select ? $query->select($select) : $query;
        $query = $with ? $query->with($with) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        return $query
                ->where('estimate_number', $estimateNumber)
                ->where('agency_id', $agencyId)
                ->firstOrFail();
    }

    /**
     * 予約番号から予約データを1件取得
     *
     */
    public function findByControlNumber(string $controlNumber, int $agencyId, array $with = [], array $select = [], bool $getDeleted = false) : ?Reserve
    {
        // $query = $this->reserve->reserve(); // スコープは"予約" → 催行済のデータにもアクセスできるようにdraftスコープは一旦外し
        $query = $this->reserve;

        $query = $select ? $query->select($select) : $query;
        $query = $with ? $query->with($with) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        return $query
                ->where('control_number', $controlNumber)
                ->where('agency_id', $agencyId)
                ->firstOrFail();
    }
    
    /**
     * ページネーションで取得
     *
     * @param int $scope 予約ステータス(予約 or 見積)
     * @var $limit
     * @return object
     */
    public function paginateByAgencyId(int $agencyId, string $applicationStep, array $params, int $limit, array $with, array $select) : LengthAwarePaginator
    {
        if ($applicationStep === config('consts.reserves.APPLICATION_STEP_RESERVE')) { // 予約データ対象
            $query = $this->reserve->reserve();
        } elseif ($applicationStep === config('consts.reserves.APPLICATION_STEP_DRAFT')) { // 予約確定前
            $query = $this->reserve->draft();
        } elseif ($applicationStep === config('consts.reserves.APPLICATION_STEP_DEPARTED')) { // 催行済みデータ対象
            $query = $this->reserve->departed();
        } else {
            $query = $this->reserve;
        }

        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;

        foreach ($params as $key => $val) {
            if (is_empty($val)) {
                continue;
            }

            if (strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_PREFIX')) === 0) { // カスタム項目
                $query = $query->whereHas('v_reserve_custom_values', function ($q) use ($key, $val) {
                    $q->where('key', $key)->where('val', 'like', "%$val%");
                });
            } elseif ($key === 'record_number') { // 見積or依頼番号
                $query = $query->where(function ($q) use ($val) {
                    $q->where('estimate_number', 'like', "%$val%")
                        ->orWhere('request_number', 'like', "%$val%");
                });
            } elseif ($key === 'departure' || $key === 'destination') { //出発地・目的地
                $query = $query->whereHas($key, function ($q) use ($key, $val) {
                    $q->where('name', 'like', "%$val%")
                        ->orWhere('name_en', 'like', "%$val%")
                        ->orWhere('code', 'like', "%$val%");
                });
            } elseif ($key === 'applicant') { // 申込(WEBユーザー)
                $query->whereHasMorph('applicant_searchable', ['App\Models\WebUser'], function (\Illuminate\Database\Eloquent\Builder $q) use ($val) {
                    $q->where('name', 'like', "%$val%")
                    ->orWhere('name_kana', 'like', "%$val%")
                    ->orWhere('name_roman', 'like', "%$val%");
                });
            } elseif ($key === 'representative') { // 代表参加者
                $query = $query->whereHas('representatives.user', function ($q) use ($key, $val) {
                    $q->where('name', 'like', "%$val%")
                    ->orWhere('name_kana', 'like', "%$val%")
                    ->orWhere('name_roman', 'like', "%$val%");
                });
            } else {
                $query = $query->where($key, 'like', "%$val%");
            }
        }

        return $query->where('reserves.agency_id', $agencyId)->sortable()->paginate($limit); // sortableする際にagency_idがリレーション先のテーブルにも存在するのでエラー回避のために明示的にagency_idを指定する
    }

    public function update(int $id, array $data): Reserve
    {
        $reserve = $this->reserve->find($id);
        $reserve->fill($data)->save();
        return $reserve;
    }

    /**
     * 項目更新
     */
    public function updateFields(int $reserveId, array $params) : bool
    {
        $this->reserve->where('id', $reserveId)->update($params);
        return true;
    }

    /**
     * 当該予約の全参加者を取得
     *
     * @param boolean $getCanceller 取消者も含むか否か
     * @return Illuminate\Support\Collection
     */
    public function getParticipants(int $reserveId, bool $getCanceller = true) : Collection
    {
        $reserve = $this->find($reserveId);
        if ($getCanceller) {
            return $reserve->participants()->with(['user'])->get();
        } else {
            return $reserve->participant_except_cancellers()->with(['user'])->get();
        }
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
        $reserve = $this->reserve->find($id);
        if ($isSoftDelete) {
            $reserve->delete();
        } else {
            $reserve->forceDelete();
        }
        return true;
    }
}
