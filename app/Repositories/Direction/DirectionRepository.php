<?php
namespace App\Repositories\Direction;

use App\Models\Direction;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class DirectionRepository implements DirectionRepositoryInterface
{
    /**
    * @param object $agency
    */
    public function __construct(Direction $direction)
    {
        $this->direction = $direction;
    }

    /**
     * 当該IDを取得
     *
     * @param int $id
     */
    public function find(int $id, array $select = []): ?Direction
    {
        return $select ? $this->direction->select($select)->find($id) : $this->direction->find($id);
    }

    /**
     * 当該UUIDを取得
     *
     * データがない場合は 404ステータス
     *
     * @param int $id
     */
    public function findByUuid(string $uuid, array $select=[]) : ?Direction
    {
        $query = $this->direction;
        $query = $select ? $query->select($select) : $query;
        return $query->where('uuid', $uuid)->firstOrFail();
    }

    /**
     * ページネーション で取得（ID用）
     *
     * @var $limit
     * @return object
     */
    public function paginateByAgencyId(int $agencyId, array $params, int $limit, array $select) : LengthAwarePaginator
    {
        $query = $this->direction;
        $query = $select ? $query->select($select) : $query;
        foreach ($params as $key => $val) {
            if (is_empty($val)) {
                continue;
            }
            $query = $query->where($key, 'like', "%$val%");
        }

        return $query->where('agency_id', $agencyId)->sortable()->paginate($limit);
    }

    public function create(array $data): Direction
    {
        return $this->direction->create($data);
    }

    public function update(int $id, array $data): Direction
    {
        $direction = $this->find($id);
        $direction->fill($data)->save();
        return $direction;
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
            $this->direction->destroy($id);
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
        $query = $this->direction;
        $query = $select ? $query->select($select) : $query;

        foreach ($where as $key => $val) {
            $query = $query->where($key, $val);
        }
        return $query->get();
    }
}
