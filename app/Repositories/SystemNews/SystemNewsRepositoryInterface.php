<?php

namespace App\Repositories\SystemNews;

use App\Models\SystemNews;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface SystemNewsRepositoryInterface
{
  public function find(int $id, array $select=[], bool $getDeleted=false): SystemNews;
  
  public function paginate(array $params, int $limit, array $with = [], array $select=[], bool $getDeleted = false) : LengthAwarePaginator;

  public function create(array $data): SystemNews;
  
  public function update(int $id, array $data): SystemNews;

  public function delete(int $id, bool $isSoftDelete): bool;
}
