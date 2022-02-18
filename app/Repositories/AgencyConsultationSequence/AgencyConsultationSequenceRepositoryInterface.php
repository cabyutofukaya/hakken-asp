<?php

namespace App\Repositories\AgencyConsultationSequence;

interface AgencyConsultationSequenceRepositoryInterface
{
    public function initCurrentNumber(int $agencyId, $date): bool;
    public function getNextNumber(int $agencyId, $date): int;
}
