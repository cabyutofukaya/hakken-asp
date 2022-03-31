<?php

namespace App\Repositories\SubjectAirplane;

use App\Models\SubjectAirplane;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface SubjectAirplaneRepositoryInterface
{
  public function find(int $id, array $select = []) : SubjectAirplane;
  public function findWhere(array $where, array $with=[], array $select=[]) : ?SubjectAirplane;
  public function paginateByAgencyId(int $agencyId, array $params, int $limit, array $with, array $select) : LengthAwarePaginator;
  public function create(array $data) : SubjectAirplane;
  public function update(int $id, array $data) : SubjectAirplane;
  public function updateField(int $subjectAirplaneId, array $params) : bool;
  public function search(int $agencyId, string $str, array $with=[], array $select=[], $limit=null, $order = 'id', $direction = 'asc') : Collection;
  public function delete(int $id, bool $isSoftDelete) : bool;
}
