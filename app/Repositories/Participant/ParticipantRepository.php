<?php
namespace App\Repositories\Participant;

use App\Models\Participant;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ParticipantRepository implements ParticipantRepositoryInterface
{
    /**
    * @param object $participant
    */
    public function __construct(Participant $participant)
    {
        $this->participant = $participant;
    }

    public function find(int $id, array $with = [], array $select = []): ?Participant
    {
        $query = $this->participant;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        return $query->find($id);
    }

    /**
     * 当該予約情報に参加済みのユーザーか否か
     */
    public function isExistsInReserve(int $userId, int $reserveId) : bool
    {
        return $this->participant->where('user_id', $userId)->where('reserve_id', $reserveId)->exists();
    }

    public function create(array $data): Participant
    {
        return $this->participant->create($data);
    }

    /**
     * バルクインサート
     */
    public function insert(array $rows) : bool
    {
        $this->participant->insert($rows);
        return true;
    }

    /**
     * 予約IDとユーザーIDリストを条件にID一覧を取得
     */
    public function getIdsByReserveIdAndUserIds(int $reserveId, array $userIds) : array
    {
        return $this->participant->select(['id'])
            ->where('reserve_id', $reserveId)->whereIn('user_id', $userIds)
            ->pluck('id')->toArray();
    }

    /**
     * ページネーションで取得
     *
     * @param int $limit 取得件数
     */
    public function paginateByReserveId(int $reserveId, array $params, int $limit, array $with=[], $select=[]) : LengthAwarePaginator
    {
        $query = $this->participant;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        
        foreach ($params as $key => $val) {
            if (is_empty($val)) {
                continue;
            }

            $query = $query->where($key, 'like', "%$val%");
        }

        return $query->where('reserve_id', $reserveId)->sortable()->paginate($limit);
    }

    /**
     * 全件取得
     */
    public function getByReserveId(int $reserveId, array $with=[], $select=[]) : Collection
    {
        $query = $this->participant;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        return $query->where('reserve_id', $reserveId)->sortable()->get();
    }

    /**
     * IDリストにマッチするレコードを全取得
     *
     * @param array $ids
     * @param array $with
     */
    public function getByIds(array $ids, array $with=[], $select=[], bool $getDeleted = false) : Collection
    {
        $query = $getDeleted ? $this->participant->withTrashed() : $this->participant;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;

        return $query->whereIn('id', $ids)->get();
    }

    /**
     * 条件で複数行更新
     *
     * @return int 作用行数
     */
    public function updateWhere(array $where, array $param) : int
    {
        $query = $this->participant;
        foreach ($where as $k => $v) {
            $query = $query->where($k, $v);
        }
        return $query->update($param);
    }

    /**
     * 項目更新
     */
    public function updateField(int $id, array $params) : bool
    {
        $this->participant->where('id', $id)->update($params);
        return true;

        // $participant = $this->participant->findOrFail($id);
        // foreach ($params as $k => $v) {
        //     $participant->{$k} = $v; // プロパティに値をセット
        // }
        // $participant->save();
        // return $participant;
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
            $this->participant->destroy($id);
        } else {
            $this->find($id)->forceDelete();
        }
        return true;
    }
}
