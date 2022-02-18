<?php

namespace App\Services;

use App\Models\ReserveSchedulePhoto;
use App\Repositories\ReserveSchedulePhoto\ReserveSchedulePhotoRepository;

class ReserveSchedulePhotoService
{
    public function __construct(ReserveSchedulePhotoRepository $reserveSchedulePhotoRepository)
    {
        $this->reserveSchedulePhotoRepository = $reserveSchedulePhotoRepository;
    }

    /**
     * 該当IDを一件取得
     *
     * @param int $id ID
     * @param array $select 取得カラム
     */
    public function find(int $id, array $with=[], array $select=[], bool $getDeleted = false): ReserveSchedulePhoto
    {
        return $this->reserveSchedulePhotoRepository->find($id, $with, $select, $getDeleted);
    }

    /**
     * 対象のs3ファイルを削除
     *
     * @param array $deleteFiles 削除ファイル一覧
     * @param boolean $isSoftDelete 論理削除か否か
     */
    public function deleteFile(string $fileName, bool $isSoftDelete = true) : bool
    {
        return $this->reserveSchedulePhotoRepository->deleteFile($fileName, $isSoftDelete);
    }
}
