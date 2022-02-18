<?php
namespace App\Repositories\City;

use App\Models\City;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CityRepository implements CityRepositoryInterface
{
    /**
    * @param object $agency
    */
    public function __construct(City $city)
    {
        $this->city = $city;
    }

    /**
     * 当該IDを取得
     *
     *
     * @param int $id
     */
    public function find(int $id, array $select = [], bool $getDeleted = false): ?City
    {
        $query = $this->city;
        
        $query = $select ? $query->select($select) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        return $query->find($id);
    }

    /**
     * 当該会社の都市・空港情報を全取得
     */
    public function allByAgencyId(string $agencyId, array $with, array $select, string $order='id', string $direction='asc') : Collection
    {
        $query = $this->city;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        return $query->where("agency_id", $agencyId)->orderBy($order, $direction)->get();
    }

    /**
     * ページネーション で取得（ID用）
     *
     * @var $limit
     * @return object
     */
    public function paginateByAgencyId(int $agencyId, array $params, int $limit, array $with, array $select) : LengthAwarePaginator
    {
        $query = $this->city;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        
        foreach ($params as $key => $val) {
            if (is_empty($val)) {
                continue;
            }
            $query = $query->where($key, 'like', "%$val%");
        }

        return $query->where('cities.agency_id', $agencyId)->sortable()->paginate($limit); // sortableする際にagency_idがリレーション先のテーブルにも存在するのでエラー回避のために明示的にagency_idを指定する
    }

    /**
     * 名称検索
     */
    public function search(int $agencyId, string $str, array $with=[], array $select=[], $limit=null) : Collection
    {
        $query = $this->city;
        
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;

        $query = $query->where("agency_id", $agencyId)->where(function ($q) use ($str) {
            $q->where("name", 'like', "%$str%")
                ->orWhere("code", 'like', "%$str%");
        });

        return !is_null($limit) ? $query->take($limit)->get() : $query->get();
    }

    public function create(array $data): City
    {
        return $this->city->create($data);
    }

    public function update(int $id, array $data): City
    {
        $city = $this->find($id);
        $city->fill($data)->save();
        return $city;
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
            $this->city->destroy($id);
        } else {
            $this->find($id)->forceDelete();
        }
        return true;
    }
}
