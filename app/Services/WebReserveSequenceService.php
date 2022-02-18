<?php

namespace App\Services;

use App\Repositories\WebReserveSequence\WebReserveSequenceRepository;

class WebReserveSequenceService
{
    public function __construct(WebReserveSequenceRepository $webReserveSequenceRepository)
    {
        $this->webReserveSequenceRepository = $webReserveSequenceRepository;
    }

    /**
     * 番号を初期化
     */
    public function initCurrentNumber(int $agencyId, $date) : bool
    {
        return $this->webReserveSequenceRepository->initCurrentNumber($agencyId, $date);
    }

    // 次の番号を取得
    public function getNextNumber(int $agencyId, $date): int
    {
        return $this->webReserveSequenceRepository->getNextNumber($agencyId, $date);
    }

}
