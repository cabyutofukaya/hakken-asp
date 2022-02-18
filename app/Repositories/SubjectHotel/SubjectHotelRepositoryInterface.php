<?php

namespace App\Repositories\SubjectHotel;

use App\Models\SubjectHotel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface SubjectHotelRepositoryInterface
{
  public function find(int $id, array $select = []) : SubjectHotel;
  public function paginateByAgencyId(int $agencyId, array $params, int $limit, array $with, array $select) : LengthAwarePaginator;
  public function create(array $data) : SubjectHotel;
  public function update(int $id, array $data) : SubjectHotel;
  public function updateField(int $subjectHotelId, array $params) : int;
  public function search(int $agencyId, string $str, array $with=[], array $select=[], $limit=null, $order = 'id', $direction = 'asc') : Collection;
  public function delete(int $id, bool $isSoftDelete) : bool;
}
