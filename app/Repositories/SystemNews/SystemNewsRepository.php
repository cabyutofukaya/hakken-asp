<?php
namespace App\Repositories\SystemNews;

use App\Models\SystemNews;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SystemNewsRepository implements SystemNewsRepositoryInterface
{
    /**
    * @param object $systemNews
    */
    public function __construct(SystemNews $systemNews)
    {
        $this->systemNews = $systemNews;
    }

    /**
     * 当該IDを取得
     *
     * データがない場合は 404ステータス
     *
     * @param int $id
     */
    public function find(int $id, array $select=[], bool $getDeleted=false): SystemNews
    {
        $query = $this->systemNews;
        $query = $select ? $query->select($select) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        return $query->findOrFail($id);
    }

    /**
     * ページネーションで取得
     */
    public function paginate(array $params, int $limit, array $with = [], array $select=[], bool $getDeleted = false) : LengthAwarePaginator
    {
        $query = $this->systemNews;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        foreach ($params as $key => $val) {
            if (is_empty($val)) {
                continue;
            }
            $query = $query->where($key, 'like', "%$val%");
        }

        return $query->sortable()->orderBy('id', 'desc')->paginate($limit);
    }

    public function create(array $data): SystemNews
    {
        return $this->systemNews->create($data);
    }

    public function update(int $id, array $data): SystemNews
    {
        $systemNews = $this->systemNews->find($id);
        $systemNews->update($data);
        return $systemNews;
    }

    /**
     * 削除
     *
     * @param int $id ID
     * @return boolean
     */
    public function delete(int $id, bool $isSoftDelete): bool
    {
        if ($isSoftDelete) {
            $this->systemNews->destroy($id);
        } else {
            $this->systemNews->find($id)->forceDelete();
        }
        return true;
    }
}
