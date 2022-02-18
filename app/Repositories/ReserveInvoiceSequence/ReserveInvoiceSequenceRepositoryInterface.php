<?php

namespace App\Repositories\ReserveInvoiceSequence;

interface ReserveInvoiceSequenceRepositoryInterface
{
    public function initCurrentNumber(int $agencyId, $date): bool;
    public function getNextNumber(int $agencyId, $date): int;
}
