<?php

namespace App\Repositories\SubjectOption;

use App\Models\SubjectOption;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface SubjectOptionRepositoryInterface
{
  public function find(int $id, array $select = []) : SubjectOption;
  public function paginateByAgencyId(int $agencyId, array $params, int $limit, array $with, array $select) : LengthAwarePaginator;
  public function create(array $data) : SubjectOption;
  public function update(int $id, array $data) : SubjectOption;
  public function updateField(int $subjectOptionId, array $params) : bool;
  public function search(int $agencyId, string $str, array $with=[], array $select=[], $limit=null, $order = 'id', $direction = 'asc') : Collection;
  public function delete(int $id, bool $isSoftDelete) : bool;
}
