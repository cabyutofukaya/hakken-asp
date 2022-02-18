<?php

namespace App\Services;

use App\Models\WebModelcoursePhoto;
use App\Repositories\WebModelcoursePhoto\WebModelcoursePhotoRepository;

class WebModelcoursePhotoService
{
    public function __construct(WebModelcoursePhotoRepository $webModelcoursePhotoRepository)
    {
        $this->webModelcoursePhotoRepository = $webModelcoursePhotoRepository;
    }

    /**
     * 画像レコードを作成
     */
    public function create(array $data) : WebModelcoursePhoto
    {
        return $this->webModelcoursePhotoRepository->create($data);
    }

    /**
     * 当該コースIDに紐づくレコードを削除
     *
     * @param int $webModelcourseId コースID
     * @param bool $softDelete 論理削除の場合はtrue
     */
    public function deleteByWebModelcourseId(int $webModelcourseId, bool $softDelete = true) : bool
    {
        return $this->webModelcoursePhotoRepository->deleteByWebModelcourseId($webModelcourseId, $softDelete);
    }
}
