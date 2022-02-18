<?php

namespace App\Repositories\AgencySequence;

interface AgencySequenceRepositoryInterface
{
    public function getNextNumber(): int;
}
