<?php
namespace App\Repositories\Area;

use App\Models\Area;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AreaRepository implements AreaRepositoryInterface
{
    /**
    * @param object $agency
    */
    public function __construct(Area $area)
    {
        $this->area = $area;
    }

    /**
     * 当該IDを取得
     *
     * @param int $id
     */
    public function find(int $id, array $select = []): ?Area
    {
        return $select ? $this->area->select($select)->find($id) : $this->area->find($id);
    }

    /**
     * 当該UUIDを取得
     *
     * データがない場合は 404ステータス
     *
     * @param string $uuid
     */
    public function findByUuid(string $uuid, array $select=[]) : ?Area
    {
        $query = $this->area;
        $query = $select ? $query->select($select) : $query;
        return $query->where('uuid', $uuid)->firstOrFail();
    }

    /**
     * ページネーション で取得（ID用）
     *
     * @var $limit
     * @return object
     */
    public function paginateByAgencyId(int $agencyId, array $params, int $limit, array $with, array $select) : LengthAwarePaginator
    {
        $query = $this->area;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        
        foreach ($params as $key => $val) {
            if (is_empty($val)) {
                continue;
            }
            $query = $query->where($key, 'like', "%$val%");
        }

        return $query->where('areas.agency_id', $agencyId)->sortable()->paginate($limit); // sortableする際にagency_idがリレーション先のテーブルにも存在するのでエラー回避のために明示的にagency_idを指定する
    }

    public function create(array $data): Area
    {
        return $this->area->create($data);
    }

    public function update(int $id, array $data): Area
    {
        $area = $this->area->find($id);
        $area->fill($data)->save();
        return $area;
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
            $this->area->destroy($id);
        } else {
            $this->find($id)->forceDelete();
        }
        return true;
    }

    /**
     * 条件に合う一覧データを取得
     */
    public function getWhere(array $where, array $select = []) : Collection
    {
        $query = $this->area;
        $query = $select ? $query->select($select) : $query;

        foreach ($where as $key => $val) {
            $query = $query->where($key, $val);
        }
        return $query->get();
    }
}
