<?php
namespace App\Repositories\AgencyNotification;

use App\Models\AgencyNotification;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AgencyNotificationRepository implements AgencyNotificationRepositoryInterface
{
    /**
    * @param object $agencyNotification
    */
    public function __construct(AgencyNotification $agencyNotification)
    {
        $this->agencyNotification = $agencyNotification;
    }

    /**
     * ページネーションで取得
     */
    public function paginateByAgencyId(int $agencyId, int $limit, array $with = [], array $select =[]) : LengthAwarePaginator
    {
        $query = $this->agencyNotification;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        
        return $query->where('agency_id', $agencyId)->orderBy('id', 'desc')->paginate($limit);
    }

    /**
     * 未読件数を取得
     */
    public function getUnreadCount(int $agencyId) : int
    {
        return $this->agencyNotification->where('agency_id', $agencyId)->whereNull('read_at')->count();
    }

    /**
     * 既読処理
     *
     * @param array $ids ID一覧
     */
    public function read(array $ids) : bool
    {
        $this->agencyNotification
            ->whereIn('id', $ids)
            ->update(['read_at' => date('Y-m-d H:i:s')]);
        return true;
    }
}
