<?php

namespace App\Services;

use App\Models\AgencyNotification;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\AgencyNotification\AgencyNotificationRepository;

/**
 * 催行済み管理
 */
class AgencyNotificationService
{
    public function __construct(AgencyNotificationRepository $agencyNotificationRepository)
    {
        $this->agencyNotificationRepository = $agencyNotificationRepository;
    }

    /**
     * ページネーションで取得
     */
    public function paginateByAgencyId(int $agencyId, int $limit, array $with = [], array $select =[])
    {
        return $this->agencyNotificationRepository->paginateByAgencyId($agencyId, $limit, $with, $select);
    }

    /**
     * 未読件数を取得
     */
    public function getUnreadCount(int $agencyId) : int
    {
        return $this->agencyNotificationRepository->getUnreadCount($agencyId);
    }

    /**
     * 既読処理
     * 
     * @param array $ids ID一覧
     */
    public function read(array $ids) : bool
    {
        $this->agencyNotificationRepository->read($ids);
        return true;
    }
}
