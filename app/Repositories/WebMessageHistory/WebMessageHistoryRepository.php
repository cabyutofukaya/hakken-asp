<?php
namespace App\Repositories\WebMessageHistory;

use App\Models\WebMessageHistory;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * reservesテーブルのweb予約データを専用に扱うリポジトリ
 */
class WebMessageHistoryRepository implements WebMessageHistoryRepositoryInterface
{
    /**
    * @param object $webMessageHistory
    */
    public function __construct(WebMessageHistory $webMessageHistory)
    {
        $this->webMessageHistory = $webMessageHistory;
    }

    /**
     * ページャーで検索
     *
     * @param int $agencyId 会社ID
     * @param array $params 検索パラメータ
     * @param int $limit 取得件数
     * @param array $with リレーション
     * @param array $select 取得項目
     * @return LengthAwarePaginator
     */
    public function paginateByAgencyId(int $agencyId, array $params = [], int $limit, array $with = [], array $select =[]) : LengthAwarePaginator
    {
        $query = $this->webMessageHistory;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        
        foreach ($params as $key => $val) {
            if (is_empty($val)) {
                continue;
            }

            if ($key==='record_number') {
                $query = $query->whereHas('reserve', function ($q) use ($val) {
                    $q->where(function ($r) use ($val) {
                        $r->where('control_number', 'like', "%$val%")
                            ->orWhere('estimate_number', 'like', "%$val%")
                            ->orWhere('request_number', 'like', "%$val%");
                    });
                });
            } elseif ($key==='application_date_from') { // 申込日
                $query = $query->whereHas('reserve.application_dates', function ($q) use ($val) {
                    $q->where('val', '>=', $val);
                });
            } elseif ($key==='application_date_to') { // 申込日
                $query = $query->whereHas('reserve.application_dates', function ($q) use ($val) {
                    $q->where('val', '<=', $val);
                });
            } elseif ($key==='received_at_from') { // 最終受信日
                $query = $query->where('last_received_at', '>=', "{$val} 00:00:00");
            } elseif ($key==='received_at_to') { // 最終受信日
                $query = $query->where('last_received_at', '<=', "{$val} 23:59:59");
            } else {
                $query = $query->where($key, 'like', "%$val%");
            }
        }

        return $query->where('web_message_histories.agency_id', $agencyId)->sortable()->paginate($limit);// sortableする際にagency_idがリレーション先のテーブルにも存在するのでエラー回避のために明示的にagency_idを指定する
    }

    /**
     * アップサート
     */
    public function updateOrCreate(array $attributes, array $values = []) : WebMessageHistory
    {
        return $this->webMessageHistory->updateOrCreate(
            $attributes,
            $values
        );
    }

    /**
     * 条件で複数行更新
     *
     * @return int 作用行数
     */
    public function updateWhere(array $where, array $param) : int
    {
        $query = $this->webMessageHistory;
        foreach ($where as $k => $v) {
            $query = $query->where($k, $v);
        }
        return $query->update($param);
    }

    /**
     * メッセージデータを取得
     */
    public function getMessage(int $reserveId) : ?string
    {
        return $this->webMessageHistory->where('reserve_id', $reserveId)->value('message_log');
    }

    /**
     * データが存在するか
     */
    public function isExistsByReserveId(int $reserveId) : bool
    {
        return $this->webMessageHistory->where('reserve_id', $reserveId)->exists();
    }
}
