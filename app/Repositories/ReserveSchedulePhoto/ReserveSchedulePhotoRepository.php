<?php
namespace App\Repositories\ReserveSchedulePhoto;

use App\Models\ReserveSchedulePhoto;
use App\Traits\DeleteImageFileTrait;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Storage;

class ReserveSchedulePhotoRepository implements ReserveSchedulePhotoRepositoryInterface
{
    use DeleteImageFileTrait;

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

    /**
     * 対象パスのs3ファイルを削除
     *
     * @param array $deleteFiles 削除ファイル一覧
     * @param boolean $isSoftDelete 論理削除か否か
     */
    public function deleteFile(string $fileName, bool $isSoftDelete = true) : bool
    {
        return $this->deleteFile($fileName, $isSoftDelete); // ファイル削除
    }
}
