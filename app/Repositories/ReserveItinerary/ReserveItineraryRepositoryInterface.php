<?php

namespace App\Repositories\ReserveItinerary;

use App\Models\ReserveItinerary;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ReserveItineraryRepositoryInterface
{
  public function find(int $id, array $with = [], array $select=[], bool $getDeleted = false) : ReserveItinerary;

  public function findByReserveItineraryNumber(string $controlNumber, int $reserveId, int $agencyId, array $with = [], array $select = [], bool $getDeleted = false) : ?ReserveItinerary;

  public function findWhere(array $where, array $with=[], array $select=[]) : ?ReserveItinerary;

  public function getByReserveId(int $reserveId, array $with=[], array $select=[]) : Collection;

  public function getCountByReserveId(int $reserveId, bool $includeDeleted = true) : int;

  public function create(array $data) : ReserveItinerary;

  public function updateWhere(array $where, array $param) : int;

  public function updateField(int $reserveItineraryId, array $params) : bool;

  public function delete(int $id, bool $isSoftDelete): bool;
}
