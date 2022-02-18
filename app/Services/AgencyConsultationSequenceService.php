<?php

namespace App\Services;

use App\Repositories\AgencyConsultationSequence\AgencyConsultationSequenceRepository;

class AgencyConsultationSequenceService
{
    public function __construct(AgencyConsultationSequenceRepository $agencyConsultationSequenceRepository)
    {
        $this->agencyConsultationSequenceRepository = $agencyConsultationSequenceRepository;
    }

    /**
     * 番号を初期化
     */
    public function initCurrentNumber(int $agencyId, $date) : bool
    {
        return $this->agencyConsultationSequenceRepository->initCurrentNumber($agencyId, $date);
    }

    // 次の番号を取得
    public function getNextNumber(int $agencyId, $date): int
    {
        return $this->agencyConsultationSequenceRepository->getNextNumber($agencyId, $date);
    }

}
