<?php

namespace App\Services;

use App\Repositories\BusinessUserConsultationSequence\BusinessUserConsultationSequenceRepository;

class BusinessUserConsultationSequenceService
{
    public function __construct(BusinessUserConsultationSequenceRepository $businessUserConsultationSequenceRepository)
    {
        $this->businessUserConsultationSequenceRepository = $businessUserConsultationSequenceRepository;
    }

    /**
     * 番号を初期化
     */
    public function initCurrentNumber(int $agencyId, $date) : bool
    {
        return $this->businessUserConsultationSequenceRepository->initCurrentNumber($agencyId, $date);
    }

    // 次の番号を取得
    public function getNextNumber(int $agencyId, $date): int
    {
        return $this->businessUserConsultationSequenceRepository->getNextNumber($agencyId, $date);
    }

}
