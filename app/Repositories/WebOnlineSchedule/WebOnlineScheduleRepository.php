<?php
namespace App\Repositories\WebOnlineSchedule;

use App\Models\WebOnlineSchedule;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class WebOnlineScheduleRepository implements WebOnlineScheduleRepositoryInterface
{
    /**
    * @param object $agency
    */
    public function __construct(WebOnlineSchedule $webOnlineSchedule)
    {
        $this->webOnlineSchedule = $webOnlineSchedule;
    }

    /**
     * リクエスト作成
     */
    public function create(array $data) : WebOnlineSchedule
    {
        return $this->webOnlineSchedule->create($data);
    }

    public function find(int $id, array $with=[], array $select=[], bool $getDeleted = false): WebOnlineSchedule
    {
        $query = $this->webOnlineSchedule;
        
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        return $query->findOrFail($id);
    }

    /**
     * 検索して1件取得
     */
    public function findWhere(array $where, array $with=[], array $select=[], bool $getDeleted = false) : ?WebOnlineSchedule
    {
        $query = $this->webOnlineSchedule;
        
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        foreach ($where as $key => $val) {
            if (is_empty($val)) {
                continue;
            }
            $query = $query->where($key, $val);
        }
        return $query->first();
    }

    public function updateFields(int $id, array $params) : bool
    {
        $this->webOnlineSchedule->where('id', $id)->update($params);
        return true;
    }

    /**
     * 条件削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue
     * @return boolean
     */
    public function deleteWhere(array $where, bool $isSoftDelete): bool
    {
        $query = $this->webOnlineSchedule;
        foreach ($where as $key => $val) {
            $query = $query->where($key, $val);
        }

        if ($isSoftDelete) {
            $query->delete();
        } else {
            $query->forceDelete();
        }
        return true;
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
            $this->webOnlineSchedule->destroy($id);
        } else {
            $this->find($id)->forceDelete();
        }
        return true;
    }
}
