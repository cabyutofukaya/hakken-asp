<?php

namespace App\Repositories\WebModelcoursePhoto;

use App\Models\WebModelcoursePhoto;

interface WebModelcoursePhotoRepositoryInterface
{
  public function create(array $data): WebModelcoursePhoto;
  
  public function deleteByWebModelcourseId(int $webModelcourseId, bool $softDelete = true) : bool;
}
