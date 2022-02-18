<?php

namespace App\Repositories\ReserveReceiptSequence;

interface ReserveReceiptSequenceRepositoryInterface
{
    public function initCurrentNumber(int $agencyId, $date): bool;
    public function getNextNumber(int $agencyId, $date): int;
}
