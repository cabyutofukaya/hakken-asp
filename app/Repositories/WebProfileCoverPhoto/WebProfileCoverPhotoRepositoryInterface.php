<?php

namespace App\Repositories\WebProfileCoverPhoto;

use App\Models\WebProfileCoverPhoto;

interface WebProfileCoverPhotoRepositoryInterface
{
  public function create(array $data): WebProfileCoverPhoto;
  
  public function deleteByWebProfileId(int $webProfileId, bool $softDelete = true) : bool;
}
