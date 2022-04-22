<?php
namespace App\Repositories\Supplier;

use App\Models\Supplier;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class SupplierRepository implements SupplierRepositoryInterface
{
    /**
    * @param object $agency
    */
    public function __construct(Supplier $supplier)
    {
        $this->supplier = $supplier;
    }

    /**
     * 当該IDを取得
     *
     * データがない場合は 404ステータス
     *
     * @param int $id
     */
    public function find(int $id, array $select = [], bool $getDeleted=false): Supplier
    {
        $query = $this->supplier;
        $query = $select ? $query->select($select) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        return $query->findOrFail($id);
    }

    /**
     * 当該会社の仕入先情報を全取得
     */
    public function allByAgencyId(string $agencyId, array $with, array $select, string $order='id', string $direction='asc', bool $getDeleted=false) : Collection
    {
        $query = $this->supplier;
        $query = $getDeleted ? $query->withTrashed() : $query;
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
        $query = $this->supplier;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        
        foreach ($params as $key => $val) {
            if (is_empty($val)) {
                continue;
            }

            if (strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_PREFIX')) === 0) { // カスタム項目
                $query = $query->whereHas('v_supplier_custom_values', function ($q) use ($key, $val) {
                    $q->where('key', $key)->where('val', 'like', "%$val%");
                });
            } else {
                $query = $query->where($key, 'like', "%$val%");
            }
        }

        return $query->where('suppliers.agency_id', $agencyId)->sortable()->paginate($limit); // sortableする際にagency_idがリレーション先のテーブルにも存在するのでエラー回避のために明示的にagency_idを指定する
    }

    public function create(array $data): Supplier
    {
        return $this->supplier->create($data);
    }

    public function update(int $id, array $data): Supplier
    {
        $supplier = $this->find($id);
        $supplier->fill($data)->save();
        return $supplier;
    }

    public function updateField(int $supplierId, array $params) : bool
    {
        $this->supplier->where('id', $supplierId)->update($params);
        return true;

        // $supplier = $this->supplier->findOrFail($supplierId);
        // foreach ($params as $k => $v) {
        //     $supplier->{$k} = $v; // プロパティに値をセット
        // }
        // $supplier->save();
        // return 1;
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
            $this->supplier->destroy($id);
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
        $query = $this->supplier;
        $query = $select ? $query->select($select) : $query;

        foreach ($where as $key => $val) {
            $query = $query->where($key, $val);
        }
        return $query->get();
    }

    /**
     * 管理コードを取得
     *
     * @param int $id ID
     */
    public function getCodeById(int $id) : ?string
    {
        return $this->supplier->where('id', $id)->value('code');
    }

    /**
     * 当該会社の仕入先登録数を取得
     *
     * @param bool $getDeleted 論理削除も含める場合はtrue
     */
    public function getCount(int $agencyId, bool $getDeleted=false) : int
    {
        $query = $this->supplier;
        $query = $getDeleted ? $query->withTrashed() : $query;

        return $query->where('agency_id', $agencyId)->count();
    }
}
