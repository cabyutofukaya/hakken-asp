<?php

namespace App\Repositories\ZoomApiKey;

use App\Models\ZoomApiKey;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ZoomApiKeyRepositoryInterface
{
  public function findRandom() : ZoomApiKey;
}
