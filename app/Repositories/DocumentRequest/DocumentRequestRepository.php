<?php
namespace App\Repositories\DocumentRequest;

use App\Models\DocumentRequest;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class DocumentRequestRepository implements DocumentRequestRepositoryInterface
{
    /**
    * @param object $agency
    */
    public function __construct(DocumentRequest $documentRequest)
    {
        $this->documentRequest = $documentRequest;
    }

    /**
     * 当該レコードを取得
     *
     * データがない場合は 404ステータス
     *
     * @param int $id
     */
    public function find(int $id, array $select = [], bool $getDeleted = false): DocumentRequest
    {
        $query = $this->documentRequest;
        $query = $select ? $query->select($select) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;
        return $query->findOrFail($id);
    }

    /**
     * 検索して1件取得
     *
     * @param array $where 条件
     * @param array $select 取得カラム
     * @return App\Models\DocumentRequest
     */
    public function findWhere(array $where, array $select = [], $getDeleted = false) : ?DocumentRequest
    {
        $query = $this->documentRequest;
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
        $query = $this->documentRequest;
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
        $query = $this->documentRequest;
        $query = $with ? $query->with($with) : $query;

        return $query->where('agency_id', $agencyId)->sortable()->paginate($limit);
    }

    /**
     * seqの最大値
     */
    public function maxSeq(int $agencyId) : int
    {
        $n = $this->documentRequest->where('agency_id', $agencyId)->max("seq");
        return is_null($n) ? 0 : $n;
    }

    public function create(array $data): DocumentRequest
    {
        return $this->documentRequest->create($data);
    }

    public function update(int $id, array $data): DocumentRequest
    {
        $documentRequest = $this->find($id);
        $documentRequest->fill($data)->save();
        return $documentRequest;
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
            $this->documentRequest->destroy($id);
        } else {
            $this->find($id)->forceDelete();
        }
        return true;
    }
}
