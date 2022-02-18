<?php

namespace App\Repositories\WebModelcourseTag;

use App\Models\WebModelcourseTag;

interface WebModelcourseTagRepositoryInterface
{
  public function deleteByWebModelcourseId(int $webModelcourseId) : bool;
}
