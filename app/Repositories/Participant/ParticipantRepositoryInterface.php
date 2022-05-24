<?php

namespace App\Repositories\Participant;

use Illuminate\Support\Collection;
use App\Models\Participant;
use Illuminate\Pagination\LengthAwarePaginator;

interface ParticipantRepositoryInterface
{
    public function find(int $id, array $with = [], array $select = []): ?Participant;

    public function isExistsInReserve(int $userId, int $reserveId) : bool;

    public function create(array $data): Participant;

    public function insert(array $rows) : bool;

    public function paginateByReserveId(int $reserveId, array $params, int $limit, array $with=[], $select=[]) : LengthAwarePaginator;
  
    public function getByReserveId(int $reserveId, array $with=[], $select=[], bool $getCanceller = false) : Collection;

    public function getIdsByReserveIdAndUserIds(int $reserveId, array $userIds) : array;

    public function getByIds(array $ids, array $with=[], $select=[]) : Collection;

    public function updateField(int $id, array $params) : bool;

    public function delete(int $id, bool $isSoftDelete): bool;
}
