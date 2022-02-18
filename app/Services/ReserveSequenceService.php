<?php

namespace App\Services;

use App\Repositories\ReserveSequence\ReserveSequenceRepository;

class ReserveSequenceService
{
    public function __construct(ReserveSequenceRepository $reserveSequenceRepository)
    {
        $this->reserveSequenceRepository = $reserveSequenceRepository;
    }

    /**
     * 番号を初期化
     */
    public function initCurrentNumber(int $agencyId, $date) : bool
    {
        return $this->reserveSequenceRepository->initCurrentNumber($agencyId, $date);
    }

    // 次の番号を取得
    public function getNextNumber(int $agencyId, $date): int
    {
        return $this->reserveSequenceRepository->getNextNumber($agencyId, $date);
    }

}
