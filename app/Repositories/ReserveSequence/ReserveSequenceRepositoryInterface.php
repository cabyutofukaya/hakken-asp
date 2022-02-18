<?php

namespace App\Repositories\ReserveSequence;

interface ReserveSequenceRepositoryInterface
{
    public function initCurrentNumber(int $agencyId, $date): bool;
    public function getNextNumber(int $agencyId, $date): int;
}
