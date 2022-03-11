<?php
namespace App\Repositories\ReserveConfirm;

use App\Models\ReserveConfirm;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ReserveConfirmRepository
{
    /**
    * @param object $reserveConfirm
    */
    public function __construct(ReserveConfirm $reserveConfirm)
    {
        $this->reserveConfirm = $reserveConfirm;
    }

    public function find(int $id, array $with = [], array $select=[], bool $getDeleted = false) : ReserveConfirm
    {
        $query = $this->reserveConfirm;
        $query = $select ? $query->select($select) : $query;
        $query = $with ? $query->with($with) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        return $query->findOrFail($id);
    }

    public function create(array $data) : ReserveConfirm
    {
        return $this->reserveConfirm->create($data);
    }

    public function update(int $id, array $data): ReserveConfirm
    {
        $reserveConfirm = $this->find($id);
        $reserveConfirm->fill($data)->save();
        return $reserveConfirm;
    }

    /**
     * ステータス更新
     * 関連モデルのタイムスタンプも更新
     */
    public function updateStatus(int $id, int $status) : bool
    {
        $reserveConfirm = $this->reserveConfirm->find($id);
        if ($reserveConfirm) {
            $reserveConfirm->status = $status;
            $reserveConfirm->save(); // 関連モデルのタイムスタンプも更新される
            return true;
        }
        return false;
    }

    /**
     * 合計金額更新
     * 関連モデルのタイムスタンプも更新
     */
    public function updateAmountTotal(int $id, int $amountTotal) : bool
    {
        $reserveConfirm = $this->reserveConfirm->find($id);
        if ($reserveConfirm) {
            $reserveConfirm->amount_total = $amountTotal;
            $reserveConfirm->save(); // 関連モデルのタイムスタンプも更新される
            return true;
        }
        return false;
    }

    /**
     * アップサート
     */
    public function updateOrCreate(array $attributes, array $values = []) : ReserveConfirm
    {
        return $this->reserveConfirm->updateOrCreate(
            $attributes,
            $values
        );
    }

    public function updateFields(int $id, array $params) : ReserveConfirm
    {
        $this->reserveConfirm->where('id', $id)->update($params);
        return $this->find($id);
        
        // $reserveConfirm = $this->find($id);
        // foreach ($params as $k => $v) {
        //     $reserveConfirm->{$k} = $v; // プロパティに値をセット
        // }
        // $reserveConfirm->save();
        // return $reserveConfirm;
    }

    /**
     * 宛名情報クリア
     *
     * @param int $reserveId 予約ID
     * @return bool
     */
    public function clearDocumentAddress(int $reserveId) : bool
    {
        $this->reserveConfirm->where('reserve_id', $reserveId)
            ->update([
                'document_address' => null,
            ]);
        return true;
    }

    /**
     * 検索して1件取得
     */
    public function findWhere(array $where, array $with=[], array $select=[]) : ?ReserveConfirm
    {
        $query = $this->reserveConfirm;
        
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
     * 全件取得
     */
    public function getByReserveItineraryId(int $reserveItineraryId, array $with=[], array $select=[], bool $getDeleted = false) : Collection
    {
        $query = $this->reserveConfirm;
        $query = $getDeleted ? $query->withTrashed() : $query;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        return $query->where('reserve_itinerary_id', $reserveItineraryId)->sortable()->get();
    }

    /**
     * 行程管理IDに対するレコード数を取得
     *
     * @param bool $includeDeleted 論理削除も含める場合はTrue
     * @return int
     */
    public function getCountByReserveItineraryId(int $reserveItineraryId, bool $includeDeleted = true) : int
    {
        if ($includeDeleted) {
            return $this->reserveConfirm->withTrashed()->where('reserve_itinerary_id', $reserveItineraryId)->count();
        } else {
            return $this->reserveConfirm->where('reserve_itinerary_id', $reserveItineraryId)->count();
        }
    }

    /**
     * 当該行程IDに紐づくdocument_quoteリレーションの中で
     * 当該codeを持つレコードに紐づくreserve_confirmレコードを一件取得
     *
     * @param int $reserveItineraryId 行程ID
     * @param string 帳票管理コード
     * @param bool $getDeleted 論理削除も取得する場合はtrue
     * @return App\Models\ReserveConfirm
     */
    public function findByDocumentQuoteCodeByReserveItineraryId(int $reserveItineraryId, string $code, array $with =[], array $select = [], bool $getDeleted = false) : ?ReserveConfirm
    {
        $query = $this->reserveConfirm;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;
        $query = $query->where('reserve_itinerary_id', $reserveItineraryId)->whereHas('document_quote', function ($q) use ($code, $getDeleted) {
            $q = $getDeleted ? $q->withTrashed() : $q;
            $q->where('code', $code);
        });
        return $query->first();
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
            $this->reserveConfirm->destroy($id);
        } else {
            $this->find($id)->forceDelete();
        }
        return true;
    }
}
