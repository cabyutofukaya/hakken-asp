<?php

namespace App\Repositories\WebProfileProfilePhoto;

use App\Models\WebProfileProfilePhoto;

interface WebProfileProfilePhotoRepositoryInterface
{
  public function create(array $data): WebProfileProfilePhoto;
  
  public function deleteByWebProfileId(int $webProfileId, bool $softDelete = true) : bool;
}
