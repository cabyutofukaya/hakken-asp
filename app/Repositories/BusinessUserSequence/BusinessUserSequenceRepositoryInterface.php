<?php

namespace App\Repositories\BusinessUserSequence;

interface BusinessUserSequenceRepositoryInterface
{
    public function initCurrentNumber(int $agencyId, $date): bool;
    public function getNextNumber(int $agencyId, $date): int;
}
