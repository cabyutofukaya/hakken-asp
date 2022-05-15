<?php
namespace App\Repositories\ReserveBase;

use App\Models\Reserve;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ReserveBaseRepository
{
    /**
    * @param object $agency
    */
    public function __construct(Reserve $reserve)
    {
        // 本リポジトリは受付の種別無く全レコードが対象
        $this->reserve = $reserve;
    }

    /**
     * 検索条件で一件取得
     */
    public function findForAgencyId(?int $id, int $agencyId, array $with = [], array $select=[], bool $getDeleted = false) : ?Reserve
    {
        $query = $this->reserve;
        $query = $select ? $query->select($select) : $query;
        $query = $with ? $query->with($with) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        return $query->where('id', $id)->where('agency_id', $agencyId)->first();
    }
}
