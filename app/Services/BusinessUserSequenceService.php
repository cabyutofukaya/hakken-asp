<?php

namespace App\Services;

use App\Repositories\BusinessUserSequence\BusinessUserSequenceRepository;

class BusinessUserSequenceService
{
    public function __construct(BusinessUserSequenceRepository $businessUserSequenceRepository)
    {
        $this->businessUserSequenceRepository = $businessUserSequenceRepository;
    }

    /**
     * 番号を初期化
     */
    public function initCurrentNumber(int $agencyId, $date) : bool
    {
        return $this->businessUserSequenceRepository->initCurrentNumber($agencyId, $date);
    }

    // 次の番号を取得
    public function getNextNumber(int $agencyId, $date): int
    {
        return $this->businessUserSequenceRepository->getNextNumber($agencyId, $date);
    }

}
