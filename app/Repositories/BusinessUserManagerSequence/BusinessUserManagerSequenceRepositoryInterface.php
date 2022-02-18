<?php

namespace App\Repositories\BusinessUserManagerSequence;

interface BusinessUserManagerSequenceRepositoryInterface
{
    public function initCurrentNumber(int $agencyId, $date): bool;
    public function getNextNumber(int $agencyId, $date): int;
}
