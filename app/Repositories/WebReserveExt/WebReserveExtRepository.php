<?php
namespace App\Repositories\WebReserveExt;

use App\Models\WebReserveExt;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class WebReserveExtRepository implements WebReserveExtRepositoryInterface
{
    /**
    * @param object $agency
    */
    public function __construct(WebReserveExt $webReserveExt)
    {
        $this->webReserveExt = $webReserveExt;
    }

    /**
     * @param bool $getDeleted 論理削除を含めるか
     */
    public function find(int $id, array $with=[], array $select=[], bool $getDeleted = false): WebReserveExt
    {
        $query = $this->webReserveExt;
        $query = $select ? $query->select($select) : $query;
        $query = $with ? $query->with($with) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        return $query->findOrFail($id);
    }

    public function updateFields(int $id, array $params) : bool
    {
        $this->webReserveExt->where('id', $id)->update($params);
        return true;
    }

    /**
     * 条件で複数行更新
     *
     * @return int 作用行数
     */
    public function updateWhere(array $where, array $param) : int
    {
        $query = $this->webReserveExt;
        foreach ($where as $k => $v) {
            $query = $query->where($k, $v);
        }
        return $query->update($param);
    }
}
