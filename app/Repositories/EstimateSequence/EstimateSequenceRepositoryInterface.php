<?php

namespace App\Repositories\EstimateSequence;

interface EstimateSequenceRepositoryInterface
{
    public function initCurrentNumber(int $agencyId, $date): bool;
    public function getNextNumber(int $agencyId, $date): int;
}
