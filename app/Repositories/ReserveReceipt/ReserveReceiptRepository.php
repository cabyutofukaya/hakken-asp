<?php
namespace App\Repositories\ReserveReceipt;

use App\Models\ReserveReceipt;
use App\Repositories\ReserveReceipt\ReserveReceiptRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ReserveReceiptRepository implements ReserveReceiptRepositoryInterface
{
    /**
    * @param object $reserveReceipt
    */
    public function __construct(ReserveReceipt $reserveReceipt)
    {
        $this->reserveReceipt = $reserveReceipt;
    }

    public function find(int $id, array $with = [], array $select=[], bool $getDeleted = false) : ?ReserveReceipt
    {
        $query = $this->reserveReceipt;
        $query = $select ? $query->select($select) : $query;
        $query = $with ? $query->with($with) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        return $query->find($id);
    }

    /**
     * 検索して1件取得
     *
     * @param array $where 条件
     * @param array $select 取得カラム
     * @return App\Models\DocumentReceipt
     */
    public function findWhere(array $where, array $with = [], array $select = [], $getDeleted = false) : ?ReserveReceipt
    {
        $query = $this->reserveReceipt;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        foreach ($where as $key => $val) {
            $query = $query->where($key, $val);
        }
        return $query->first();
    }

    public function create(array $data): ReserveReceipt
    {
        return $this->reserveReceipt->create($data);
    }

    /**
     * アップサート
     */
    public function updateOrCreate(array $attributes, array $values = []) : ReserveReceipt
    {
        return $this->reserveReceipt->updateOrCreate(
            $attributes,
            $values
        );
    }

    /**
     * ステータス更新
     */
    public function updateStatus(int $id, int $status) : bool
    {
        $reserveReceipt = $this->reserveReceipt->find($id);
        if ($reserveReceipt) {
            $reserveReceipt->status = $status;
            $reserveReceipt->save(); // 関連モデルのタイムスタンプも更新される
            return true;
        }
        return false;
    }

    /**
     * 宛名情報クリア
     *
     * @param int $reserveId 予約ID
     * @return bool
     */
    public function clearDocumentAddress(int $reserveId) : bool
    {
        $this->reserveReceipt->where('reserve_id', $reserveId)
            ->update([
                'document_address' => null,
            ]);
        return true;
    }

}
