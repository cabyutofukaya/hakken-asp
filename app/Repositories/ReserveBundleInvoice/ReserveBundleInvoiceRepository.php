<?php
namespace App\Repositories\ReserveBundleInvoice;

use App\Models\ReserveBundleInvoice;
use App\Repositories\ReserveBundleInvoice\ReserveBundleInvoiceRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ReserveBundleInvoiceRepository implements ReserveBundleInvoiceRepositoryInterface
{
    /**
    * @param object $reserveBundleInvoice
    */
    public function __construct(ReserveBundleInvoice $reserveBundleInvoice)
    {
        $this->reserveBundleInvoice = $reserveBundleInvoice;
    }

    /**
     * 当該IDを一件取得
     */
    public function find(int $id, array $with = [], array $select = [], bool $getDeleted = false) : ReserveBundleInvoice
    {
        $query = $this->reserveBundleInvoice;
        $query = $select ? $query->select($select) : $query;
        $query = $with ? $query->with($with) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        return $query->findOrFail($id);
    }

    /**
     * 一括請求情報が存在するか
     *
     * @param int $businessUserId 法人顧客ID
     * @param string $cutoffDate 請求締日(YYYY-MM-DD)
     * @return bool
     */
    public function isExistInvoice(int $businessUserId, string $cutoffDate) : bool
    {
        return $this->reserveBundleInvoice->where('business_user_id', $businessUserId)->where('cutoff_date', $cutoffDate)->exists();
    }

    /**
     * 検索して1件取得
     */
    public function findWhere(array $where, array $with=[], array $select=[]) : ?ReserveBundleInvoice
    {
        $query = $this->reserveBundleInvoice;
        
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;

        foreach ($where as $key => $val) {
            if (is_empty($val)) {
                continue;
            }
            $query = $query->where($key, $val);
        }
        return $query->first();
    }

    /**
     * 作成
     */
    public function create(array $data) : ReserveBundleInvoice
    {
        return $this->reserveBundleInvoice->create($data);
    }

    /**
     * 更新
     */
    public function update(int $id, array $data): ReserveBundleInvoice
    {
        $reserveBundleInvoice = $this->find($id);
        $reserveBundleInvoice->fill($data)->save();
        return $reserveBundleInvoice;
    }

    /**
     * ステータス更新
     */
    public function updateStatus(int $id, int $status) : bool
    {
        $reserveBundleInvoice = $this->reserveBundleInvoice->find($id);
        if ($reserveBundleInvoice) {
            $reserveBundleInvoice->status = $status;
            $reserveBundleInvoice->save(); // 関連モデルのタイムスタンプも更新される
            return true;
        }
        return false;
    }

    /**
     * 項目更新
     */
    public function updateFields(int $id, array $params) : bool
    {
        $this->reserveBundleInvoice->where('id', $id)->update($params);
        return true;

        // $reserveBundleInvoice = $this->reserveBundleInvoice->findOrFail($id);
        // foreach ($params as $k => $v) {
        //     $reserveBundleInvoice->{$k} = $v; // プロパティに値をセット
        // }
        // $reserveBundleInvoice->save();
        // return true;
    }

    /**
     * 子レコードがなければ削除
     *
     * @param bool $isSoftDelete 論理削除か否か
     */
    public function deleteIfNoChild(int $id, bool $isSoftDelete = true) : void
    {
        $reserveBundleInvoice = $this->reserveBundleInvoice->find($id);
        if ($reserveBundleInvoice && $reserveBundleInvoice->reserve_invoices->isEmpty()) {
            if ($isSoftDelete) {
                $reserveBundleInvoice->delete();
            } else {
                $reserveBundleInvoice->forceDelete();
            }
        }
    }
}
