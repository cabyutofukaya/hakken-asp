<?php

namespace App\Repositories\WebReserveSequence;

interface WebReserveSequenceRepositoryInterface
{
    public function initCurrentNumber(int $agencyId, $date): bool;
    public function getNextNumber(int $agencyId, $date): int;
}
