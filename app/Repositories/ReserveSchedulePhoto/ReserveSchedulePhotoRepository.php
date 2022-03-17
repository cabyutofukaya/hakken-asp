<?php
namespace App\Repositories\ReserveSchedulePhoto;

use App\Models\ReserveSchedulePhoto;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Storage;

class ReserveSchedulePhotoRepository implements ReserveSchedulePhotoRepositoryInterface
{
    /**
    * @param object $reserveSchedulePhoto
    */
    public function __construct(ReserveSchedulePhoto $reserveSchedulePhoto)
    {
        $this->reserveSchedulePhoto = $reserveSchedulePhoto;
    }

    public function find(int $id, array $with=[], array $select=[], bool $getDeleted = false): ReserveSchedulePhoto
    {
        $query = $this->reserveSchedulePhoto;
        $query = $select ? $query->select($select) : $query;
        $query = $with ? $query->with($with) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        return $query->findOrFail($id);
    }

}
