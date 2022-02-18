<?php

namespace App\Services;

use App\Repositories\UserSequence\UserSequenceRepository;

class UserSequenceService
{
    public function __construct(UserSequenceRepository $userSequenceRepository)
    {
        $this->userSequenceRepository = $userSequenceRepository;
    }

    /**
     * 番号を初期化
     */
    public function initCurrentNumber(int $agencyId, $date) : bool
    {
        return $this->userSequenceRepository->initCurrentNumber($agencyId, $date);
    }

    // 次の番号を取得
    public function getNextNumber(int $agencyId, $date): int
    {
        return $this->userSequenceRepository->getNextNumber($agencyId, $date);
    }

}
