<?php

namespace App\Services;

use App\Models\WebProfileCoverPhoto;
use App\Repositories\WebProfileCoverPhoto\WebProfileCoverPhotoRepository;

class WebProfileCoverPhotoService
{
    public function __construct(WebProfileCoverPhotoRepository $webProfileCoverPhotoRepository)
    {
        $this->webProfileCoverPhotoRepository = $webProfileCoverPhotoRepository;
    }

    /**
     * プロフィール画像レコードを作成
     */
    public function create(array $data) : WebProfileCoverPhoto
    {
        return $this->webProfileCoverPhotoRepository->create($data);
    }

    /**
     * 当該プロフィールIDに紐づくレコードを削除
     *
     * @param int $webProfileId プロフィールID
     * @param bool $softDelete 論理削除の場合はtrue
     */
    public function deleteByWebProfileId(int $webProfileId, bool $softDelete = true) : bool
    {
        return $this->webProfileCoverPhotoRepository->deleteByWebProfileId($webProfileId, $softDelete);
    }
}
