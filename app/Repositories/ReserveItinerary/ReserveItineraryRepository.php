<?php
namespace App\Repositories\ReserveItinerary;

use App\Models\ReserveItinerary;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ReserveItineraryRepository implements ReserveItineraryRepositoryInterface
{
    /**
    * @param object $reserveItinerary
    */
    public function __construct(ReserveItinerary $reserveItinerary)
    {
        $this->reserveItinerary = $reserveItinerary;
    }

    public function find(int $id, array $with = [], array $select=[], bool $getDeleted = false) : ReserveItinerary
    {
        $query = $this->reserveItinerary;
        $query = $select ? $query->select($select) : $query;
        $query = $with ? $query->with($with) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        return $query->findOrFail($id);
    }

    /**
     * 予約ID、行程番号から行程データを1件取得
     */
    public function findByReserveItineraryNumber(string $controlNumber, int $reserveId, int $agencyId, array $with = [], array $select = [], bool $getDeleted = false) : ?ReserveItinerary
    {
        $query = $this->reserveItinerary;
        $query = $select ? $query->select($select) : $query;
        $query = $with ? $query->with($with) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        return $query->where('reserve_id', $reserveId)->where('control_number', $controlNumber)->where('agency_id', $agencyId)->firstOrFail();
    }

    /**
     * 予約IDに対するレコード数を取得
     *
     * @param bool $includeDeleted 論理削除も含める場合はTrue
     * @return int
     */
    public function getCountByReserveId(int $reserveId, bool $includeDeleted = true) : int
    {
        if ($includeDeleted) {
            return $this->reserveItinerary->withTrashed()->where('reserve_id', $reserveId)->count();
        } else {
            return $this->reserveItinerary->where('reserve_id', $reserveId)->count();
        }
    }

    public function create(array $data) : ReserveItinerary
    {
        return $this->reserveItinerary->create($data);
    }

    /**
     * 全件取得
     */
    public function getByReserveId(int $reserveId, array $with=[], array $select=[]) : Collection
    {
        $query = $this->reserveItinerary;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        return $query->where('reserve_id', $reserveId)->sortable()->get();
    }

    /**
     * 条件で複数行更新
     * 
     * @return int 作用行数
     */
    public function updateWhere(array $where, array $param) : int
    {
        $query = $this->reserveItinerary;
        foreach($where as $k => $v){
            $query = $query->where($k, $v);
        }
        return $query->update($param);
    }

    /**
     * 項目更新
     */
    public function updateField(int $reserveItineraryId, array $params) : bool
    {
        $this->reserveItinerary->where('id', $reserveItineraryId)->update($params);
        return true;
        // return $this->reserveItinerary->findOrFail($reserveItineraryId);

        // $reserveItinerary = $this->reserveItinerary->findOrFail($reserveItineraryId);
        // foreach ($params as $k => $v) {
        //     $reserveItinerary->{$k} = $v; // プロパティに値をセット
        // }
        // $reserveItinerary->save();
        // return $reserveItinerary;
    }

    /**
     * 検索して1件取得
     */
    public function findWhere(array $where, array $with=[], array $select=[]) : ?ReserveItinerary
    {
        $query = $this->reserveItinerary;
        
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
     * 削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue
     * @return boolean
     */
    public function delete(int $id, bool $isSoftDelete): bool
    {
        if ($isSoftDelete) {
            $this->reserveItinerary->destroy($id);
        } else {
            $this->find($id)->forceDelete();
        }
        return true;
    }

}