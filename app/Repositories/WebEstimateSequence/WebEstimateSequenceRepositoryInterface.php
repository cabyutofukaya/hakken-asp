<?php

namespace App\Repositories\WebEstimateSequence;

interface WebEstimateSequenceRepositoryInterface
{
    public function initCurrentNumber(int $agencyId, $date): bool;
    public function getNextNumber(int $agencyId, $date): int;
}
