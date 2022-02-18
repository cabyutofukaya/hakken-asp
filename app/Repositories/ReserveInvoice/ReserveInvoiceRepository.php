<?php
namespace App\Repositories\ReserveInvoice;

use App\Models\ReserveInvoice;
use App\Repositories\ReserveInvoice\ReserveInvoiceRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ReserveInvoiceRepository implements ReserveInvoiceRepositoryInterface
{
    /**
    * @param object $reserveInvoice
    */
    public function __construct(ReserveInvoice $reserveInvoice)
    {
        $this->reserveInvoice = $reserveInvoice;
    }

    public function find(int $id, array $with = [], array $select=[], bool $getDeleted = false) : ReserveInvoice
    {
        $query = $this->reserveInvoice;
        $query = $select ? $query->select($select) : $query;
        $query = $with ? $query->with($with) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        return $query->findOrFail($id);
    }

    public function findByReserveId(int $reserveId, array $with = [], array $select=[], bool $getDeleted = false) : ?ReserveInvoice
    {
        $query = $this->reserveInvoice;
        $query = $select ? $query->select($select) : $query;
        $query = $with ? $query->with($with) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        return $query->where('reserve_id', $reserveId)->first();
    }

    /**
     * reserve_bundle_invoice_idに紐づくレコードをページネーションで取得
     *
     * @param int $reserveBundleInvoiceId
     * @var $limit
     * @return object
     */
    public function paginateByReserveBundleInvoiceId(int $agencyId, int $reserveBundleInvoiceId, int $limit, array $with = [], array $select = [], bool $getDeleted = false) : LengthAwarePaginator
    {
        $query = $this->reserveInvoice;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        $query = $query->where('reserve_invoices.agency_id', $agencyId)->where('reserve_bundle_invoice_id', $reserveBundleInvoiceId);
        // sortableする際にagency_idがリレーション先のテーブルにも存在するのでエラー回避のために明示的にagency_idを指定する

        return $query->sortable()->paginate($limit);
    }

    /**
     * 条件で全取得
     */
    public function getWhere(array $where, array $with = [], array $select = [], bool $getDeleted = false): Collection
    {
        $query = $this->reserveInvoice;
        $query = $getDeleted ? $query->withTrashed() : $query;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;

        foreach ($where as $key => $val) {
            $query = $query->where($key, $val);
        }
        return $query->get();
    }

    /**
     * wherein指定で全取得
     */
    public function getWhereIn(string $column, array $vals, array $with = [], array $select = [], bool $getDeleted = false): Collection
    {
        $query = $this->reserveInvoice;
        $query = $getDeleted ? $query->withTrashed() : $query;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        $query = $query->whereIn($column, $vals);
        return $query->get();
    }

    /**
     * アップサート
     */
    public function updateOrCreate(array $attributes, array $values = []) : ReserveInvoice
    {
        return $this->reserveInvoice->updateOrCreate(
            $attributes,
            $values
        );
    }

    /**
     * 項目更新
     */
    public function updateFields(int $reserveInvoiceId, array $params) : bool
    {
        $this->reserveInvoice->where('id', $reserveInvoiceId)->update($params);
        return true;
        // $reserveInvoice = $this->reserveInvoice->findOrFail($reserveInvoiceId);
        // foreach ($params as $k => $v) {
        //     $reserveInvoice->{$k} = $v; // プロパティに値をセット
        // }
        // $reserveInvoice->save();
        // return true;
    }


    /**
     * 宛名情報クリア
     *
     * @param int $reserveId 予約ID
     * @return bool
     */
    public function clearDocumentAddress(int $reserveId) : bool
    {
        $this->reserveInvoice->where('reserve_id', $reserveId)
            ->update([
                // 'business_user_id' => null, business_user_idをNullにしてしまうとまずいと思うので一旦コメントアウト
                'billing_address_name' => null,
                'document_address' => null,
            ]);
        return true;
    }
}
