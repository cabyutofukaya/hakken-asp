<?php
namespace App\Repositories\DocumentQuote;

use App\Models\DocumentQuote;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class DocumentQuoteRepository implements DocumentQuoteRepositoryInterface
{
    /**
    * @param object $agency
    */
    public function __construct(DocumentQuote $documentQuote)
    {
        $this->documentQuote = $documentQuote;
    }

    /**
     * 当該レコードを取得
     *
     * データがない場合は 404ステータス
     *
     * @param int $id
     */
    public function find(int $id, array $with = [], array $select = [], bool $getDeleted = false): DocumentQuote
    {
        $query = $this->documentQuote;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;
        return $query->findOrFail($id);
    }

    /**
     * 検索して1件取得
     *
     * @param array $where 条件
     * @param array $select 取得カラム
     * @return App\Models\DocumentQuote
     */
    public function findWhere(array $where, array $select = []) : ?DocumentQuote
    {
        $query = $this->documentQuote;
        $query = $select ? $query->select($select) : $query;

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
        $query = $this->documentQuote;
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
        $query = $this->documentQuote;
        $query = $with ? $query->with($with) : $query;

        return $query->where('agency_id', $agencyId)->sortable()->paginate($limit);
    }

    /**
     * 帳票として追加可能なテンプレート一覧を取得
     *
     * @param int $agencyId 会社ID
     * @param array $nonAppendableCodes 追加不可のコード一覧
     * @param array $select 取得カラム
     * @param bool $getDeleted 論理削除も取得する場合はtrue
     * @param string $order ソートカラム
     * @param string $direction ソート方法
     */
    public function getAppendableTemplates(int $agencyId, array $nonAppendableCodes, array $select = [], bool $getDeleted = false, string $order = "seq", string $direction = "asc") : Collection
    {
        $query = $this->documentQuote;
        $query = $select ? $query->select($select) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;
        $query = $query->where('agency_id', $agencyId)->where(function ($q) use ($nonAppendableCodes) {
            $q->whereNull('code')
                ->orWhereNotIn('code', $nonAppendableCodes);
        });
        return $query->orderBy($order, $direction)->get();
    }

    /**
     * seqの最大値
     */
    public function maxSeq(int $agencyId) : int
    {
        $n = $this->documentQuote->where('agency_id', $agencyId)->max("seq");
        return is_null($n) ? 0 : $n;
    }

    public function create(array $data): DocumentQuote
    {
        return $this->documentQuote->create($data);
    }

    public function update(int $id, array $data): DocumentQuote
    {
        $documentQuote = $this->find($id);
        $documentQuote->fill($data)->save();
        return $documentQuote;
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
            $this->documentQuote->destroy($id);
        } else {
            $this->find($id)->forceDelete();
        }
        return true;
    }
}
