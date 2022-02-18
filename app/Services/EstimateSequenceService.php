<?php

namespace App\Services;

use App\Repositories\EstimateSequence\EstimateSequenceRepository;

class EstimateSequenceService
{
    public function __construct(EstimateSequenceRepository $estimateSequenceRepository)
    {
        $this->estimateSequenceRepository = $estimateSequenceRepository;
    }

    /**
     * 番号を初期化
     */
    public function initCurrentNumber(int $agencyId, $date) : bool
    {
        return $this->estimateSequenceRepository->initCurrentNumber($agencyId, $date);
    }

    // 次の番号を取得
    public function getNextNumber(int $agencyId, $date): int
    {
        return $this->estimateSequenceRepository->getNextNumber($agencyId, $date);
    }

}
