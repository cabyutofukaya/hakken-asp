<?php

namespace App\Repositories\WebProfileTag;

use App\Models\WebProfileTag;

interface WebProfileTagRepositoryInterface
{
  public function deleteByWebProfileId(int $webProfileId) : bool;
}
