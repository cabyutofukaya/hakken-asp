<?php
namespace App\Repositories\DocumentRequestAll;

use App\Models\DocumentRequestAll;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class DocumentRequestAllRepository implements DocumentRequestAllRepositoryInterface
{
    /**
    * @param object $agency
    */
    public function __construct(DocumentRequestAll $documentRequestAll)
    {
        $this->documentRequestAll = $documentRequestAll;
    }

    /**
     * 当該レコードを取得
     *
     * データがない場合は 404ステータス
     *
     * @param int $id
     */
    public function find(int $id, array $select = [], bool $getDeleted = false): DocumentRequestAll
    {
        $query = $this->documentRequestAll;
        $query = $select ? $query->select($select) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;
        return $query->findOrFail($id);
    }


    /**
     * 検索して1件取得
     *
     * @param array $where 条件
     * @param array $select 取得カラム
     * @return App\Models\DocumentRequestAll
     */
    public function findWhere(array $where, array $select = [], $getDeleted = false) : ?DocumentRequestAll
    {
        $query = $this->documentRequestAll;
        $query = $select ? $query->select($select) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        foreach ($where as $key => $val) {
            $query = $query->where($key, $val);
        }
        return $query->first();
    }

    /**
     * 検索して取得
     */
    public function getWhere(array $where, array $select = [], bool $getDeleted = false, $order = "seq", $direction = "asc"): Collection
    {
        $query = $this->documentRequestAll;
        $query = $select ? $query->select($select) : $query;

        foreach ($where as $key => $val) {
            $query = $query->where($key, $val);
        }
        
        $query = $getDeleted ? $query->withTrashed() : $query;
        return $query->orderBy($order, $direction)->get();
    }

    /**
     * 一覧をページネーションで取得
     *
     * @var $limit
     * @return Illuminate\Pagination\LengthAwarePaginator
     */
    public function paginateByAgencyId(int $agencyId, int $limit, array $with) : LengthAwarePaginator
    {
        $query = $this->documentRequestAll;
        $query = $with ? $query->with($with) : $query;

        return $query->where('agency_id', $agencyId)->sortable()->paginate($limit);
    }

    /**
     * seqの最大値
     */
    public function maxSeq(int $agencyId) : int
    {
        $n = $this->documentRequestAll->where('agency_id', $agencyId)->max("seq");
        return is_null($n) ? 0 : $n;
    }

    public function create(array $data): DocumentRequestAll
    {
        return $this->documentRequestAll->create($data);
    }

    public function update(int $id, array $data): DocumentRequestAll
    {
        $documentRequestAll = $this->find($id);
        $documentRequestAll->fill($data)->save();
        return $documentRequestAll;
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
            $this->documentRequestAll->destroy($id);
        } else {
            $this->find($id)->forceDelete();
        }
        return true;
    }
}
