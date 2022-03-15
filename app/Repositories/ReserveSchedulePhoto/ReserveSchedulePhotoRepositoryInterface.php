<?php

namespace App\Repositories\ReserveSchedulePhoto;

use App\Models\ReserveSchedulePhoto;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ReserveSchedulePhotoRepositoryInterface
{
  public function find(int $id, array $with=[], array $select=[], bool $getDeleted = false): ReserveSchedulePhoto;
}
