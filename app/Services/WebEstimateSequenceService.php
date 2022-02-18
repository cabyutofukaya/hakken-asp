<?php

namespace App\Services;

use App\Repositories\WebEstimateSequence\WebEstimateSequenceRepository;

class WebEstimateSequenceService
{
    public function __construct(WebEstimateSequenceRepository $webEstimateSequenceRepository)
    {
        $this->webEstimateSequenceRepository = $webEstimateSequenceRepository;
    }

    /**
     * 番号を初期化
     */
    public function initCurrentNumber(int $agencyId, $date) : bool
    {
        return $this->webEstimateSequenceRepository->initCurrentNumber($agencyId, $date);
    }

    // 次の番号を取得
    public function getNextNumber(int $agencyId, $date): int
    {
        return $this->webEstimateSequenceRepository->getNextNumber($agencyId, $date);
    }

}
