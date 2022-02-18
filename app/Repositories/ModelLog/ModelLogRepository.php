<?php
namespace App\Repositories\ModelLog;

use App\Models\ModelLog;
use Illuminate\Pagination\LengthAwarePaginator;

class ModelLogRepository implements ModelLogRepositoryInterface
{
    protected $modelLog;

    /**
    * @param object $modelLog
    */
    public function __construct(ModelLog $modelLog)
    {
        $this->modelLog = $modelLog;
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
        $query = $this->modelLog;
        foreach ($conditions as $col => $value) {
            if (strtoupper($andOr) === 'AND') {
                $query = $query->where($col, $value);
            } elseif (strtoupper($andOr) === 'OR') {
                $query = $query->orWhere($col, $value);
            }
        }
        return $query->sortable()->orderBy($order, $orderType)->paginate($limit);
    }

    public function find(int $id): ?ModelLog
    {
        return $this->modelLog->find($id);
    }
}
