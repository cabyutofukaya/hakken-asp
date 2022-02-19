<?php
namespace App\Repositories\ReserveDeparted;

use App\Models\Reserve;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

// 催行済予約
class ReserveDepartedRepository implements ReserveDepartedRepositoryInterface
{
    /**
    * @param object $agency
    */
    public function __construct(Reserve $reserve)
    {
        // 本リポジトリは催行済レコードのみ対象
        $this->reserve = $reserve->departed();
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
     * 予約番号から催行済データを1件取得
     *
     */
    public function findByControlNumber(string $controlNumber, int $agencyId, array $with = [], array $select = [], bool $getDeleted = false) : ?Reserve
    {
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
    public function paginateByAgencyId(int $agencyId, array $params, int $limit, array $with, array $select) : LengthAwarePaginator
    {
        $query = $this->reserve;
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
            } elseif ($key === 'departure' || $key === 'destination') { //出発地・目的地
                $query = $query->whereHas($key, function ($q) use ($key, $val) {
                    $q->where('name', 'like', "%$val%")
                        ->orWhere('name_en', 'like', "%$val%")
                        ->orWhere('code', 'like', "%$val%");
                });
            } elseif ($key === 'applicant') { // 申込者。個人顧客(ASPユーザー) or 法人顧客
                $query->whereHasMorph('applicant_searchable', ['App\Models\AspUser','App\Models\BusinessUserManager'], function (\Illuminate\Database\Eloquent\Builder $q) use ($val) {
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
