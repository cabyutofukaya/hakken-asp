<?php

namespace App\Repositories\Reserve;

use App\Models\Reserve;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ReserveRepositoryInterface
{
    public function find(int $id, array $with = [], array $select=[], bool $getDeleted = false): Reserve;

    public function findByEstimateNumber(string $estimateNumber, int $agencyId, array $with = [], array $select = [], bool $getDeleted = false) : ?Reserve;

    public function findByControlNumber(string $controlNumber, int $agencyId, array $with = [], array $select = [], bool $getDeleted = false) : ?Reserve;

    public function paginateByAgencyId(int $agencyId, string $applicationStep, array $params, int $limit, array $with, array $select) : LengthAwarePaginator;

    public function paginateByUserId(int $userId, int $agencyId, array $params, int $limit, array $with, array $select) : LengthAwarePaginator;

    public function paginateByBusinessUserId(int $businessUserId, int $agencyId, array $params, int $limit, array $with, array $select) : LengthAwarePaginator;

    public function detachParticipant(int $resesrveId, int $participantId) : bool;

    // public function getParticipants(int $reserveId, bool $getCanceller = true) : Collection;

    public function create(array $data) : Reserve;

    public function update(int $id, array $data): Reserve;

    public function updateFields(int $reserveId, array $params) : bool;

    public function delete(int $id, bool $isSoftDelete): bool;
}
