<?php

namespace App\Repositories\AgencyConsultation;

use App\Models\AgencyConsultation;
use Illuminate\Pagination\LengthAwarePaginator;

interface AgencyConsultationRepositoryInterface
{
  public function find(int $id, array $select = []): ?AgencyConsultation;

  public function paginateByAgencyId(int $agencyId, array $params = [], int $limit, array $with = [], array $select =[]) : LengthAwarePaginator;

  public function getIncompleteCount(string $taxonomy, int $id) :int;

  public function create(array $data) : AgencyConsultation;

  public function update(int $id, array $data): AgencyConsultation;

  public function findWhere(array $where, array $with = [], array $select = []) : ?AgencyConsultation;
}
