<?php
namespace App\Repositories\ReserveBundleReceipt;

use App\Models\ReserveBundleReceipt;
use App\Repositories\ReserveBundleReceipt\ReserveBundleReceiptRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ReserveBundleReceiptRepository implements ReserveBundleReceiptRepositoryInterface
{
    /**
    * @param object $reserveBundleReceipt
    */
    public function __construct(ReserveBundleReceipt $reserveBundleReceipt)
    {
        $this->reserveBundleReceipt = $reserveBundleReceipt;
    }

    public function find(int $id, array $with = [], array $select=[], bool $getDeleted = false) : ReserveBundleReceipt
    {
        $query = $this->reserveBundleReceipt;
        $query = $select ? $query->select($select) : $query;
        $query = $with ? $query->with($with) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        return $query->findOrFail($id);
    }

    /**
     * 検索して1件取得
     */
    public function findWhere(array $where, array $with=[], array $select=[], bool $getDeleted = false) : ?ReserveBundleReceipt
    {
        $query = $this->reserveBundleReceipt;
        
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

    /**
     * アップサート
     */
    public function updateOrCreate(array $attributes, array $values = []) : ReserveBundleReceipt
    {
        return $this->reserveBundleReceipt->updateOrCreate(
            $attributes,
            $values
        );
    }

    /**
     * ステータス更新
     */
    public function updateStatus(int $id, int $status) : bool
    {
        $reserveBundleReceipt = $this->reserveBundleReceipt->find($id);
        if ($reserveBundleReceipt) {
            $reserveBundleReceipt->status = $status;
            $reserveBundleReceipt->save(); // 関連モデルのタイムスタンプも更新される
            return true;
        }
        return false;
    }

}
