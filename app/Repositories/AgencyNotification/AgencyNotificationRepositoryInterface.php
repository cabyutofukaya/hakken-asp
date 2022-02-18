<?php

namespace App\Repositories\AgencyNotification;

use Illuminate\Support\Collection;
use App\Models\AgencyNotification;
use Illuminate\Pagination\LengthAwarePaginator;

interface AgencyNotificationRepositoryInterface
{
  public function read(array $ids) : bool;
  
  public function paginateByAgencyId(int $agencyId, int $limit, array $with = [], array $select =[]) : LengthAwarePaginator;

  public function getUnreadCount(int $agencyId) : int;
}