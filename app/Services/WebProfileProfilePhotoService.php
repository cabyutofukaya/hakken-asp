<?php

namespace App\Services;

use App\Models\WebProfileProfilePhoto;
use App\Repositories\WebProfileProfilePhoto\WebProfileProfilePhotoRepository;

class WebProfileProfilePhotoService
{
    public function __construct(WebProfileProfilePhotoRepository $webProfileProfilePhotoRepository)
    {
        $this->webProfileProfilePhotoRepository = $webProfileProfilePhotoRepository;
    }

    /**
     * プロフィール画像レコードを作成
     */
    public function create(array $data) : WebProfileProfilePhoto
    {
        return $this->webProfileProfilePhotoRepository->create($data);
    }

    /**
     * 当該プロフィールIDに紐づくレコードを削除
     * 
     * @param int $webProfileId プロフィールID
     * @param bool $softDelete 論理削除の場合はtrue
     */
    public function deleteByWebProfileId(int $webProfileId, bool $softDelete = true) : bool
    {
        return $this->webProfileProfilePhotoRepository->deleteByWebProfileId($webProfileId, $softDelete);
    }
}
