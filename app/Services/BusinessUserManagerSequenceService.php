<?php

namespace App\Services;

use App\Repositories\BusinessUserManagerSequence\BusinessUserManagerSequenceRepository;

class BusinessUserManagerSequenceService
{
    public function __construct(BusinessUserManagerSequenceRepository $BusinessUserManagerSequenceRepository)
    {
        $this->BusinessUserManagerSequenceRepository = $BusinessUserManagerSequenceRepository;
    }

    /**
     * 番号を初期化
     */
    public function initCurrentNumber(int $agencyId, $date) : bool
    {
        return $this->BusinessUserManagerSequenceRepository->initCurrentNumber($agencyId, $date);
    }

    // 次の番号を取得
    public function getNextNumber(int $agencyId, $date): int
    {
        return $this->BusinessUserManagerSequenceRepository->getNextNumber($agencyId, $date);
    }

}
