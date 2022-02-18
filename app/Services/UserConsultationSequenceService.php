<?php

namespace App\Services;

use App\Repositories\UserConsultationSequence\UserConsultationSequenceRepository;

class UserConsultationSequenceService
{
    public function __construct(UserConsultationSequenceRepository $userConsultationSequenceRepository)
    {
        $this->userConsultationSequenceRepository = $userConsultationSequenceRepository;
    }

    /**
     * 番号を初期化
     */
    public function initCurrentNumber(int $agencyId, $date) : bool
    {
        return $this->userConsultationSequenceRepository->initCurrentNumber($agencyId, $date);
    }

    // 次の番号を取得
    public function getNextNumber(int $agencyId, $date): int
    {
        return $this->userConsultationSequenceRepository->getNextNumber($agencyId, $date);
    }

}
