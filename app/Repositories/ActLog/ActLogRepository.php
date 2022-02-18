<?php
namespace App\Repositories\ActLog;

use App\Models\ActLog;
use Illuminate\Pagination\LengthAwarePaginator;

class ActLogRepository implements ActLogRepositoryInterface
{
    /**
    * @param object $actLog
    */
    public function __construct(ActLog $actLog)
    {
        $this->actLog = $actLog;
    }

        /**
     * ページネーション で取得
     *
     * @param int $limit 取得件数
     * @param array $conditions 条件
     * @param string $andOr 比較演算子（AND / OR）
     * @param string $order 
     * @param string $orderType
     * @return object
     */
    public function paginate(int $limit, ?array $conditions, ?string $andOr, ?string $order, ?string $orderType): LengthAwarePaginator
    {
        $query = $this->actLog;
        foreach ($conditions as $col => $value) {
            if (strtoupper($andOr) === 'AND') {
                $query = $query->where($col, $value);
            } elseif (strtoupper($andOr) === 'OR') {
                $query = $query->orWhere($col, $value);
            }
        }
        return $query->sortable()->orderBy($order, $orderType)->paginate($limit);
    }

    public function create(array $data): void
    {
        $this->actLog->create($data);
    }
}
