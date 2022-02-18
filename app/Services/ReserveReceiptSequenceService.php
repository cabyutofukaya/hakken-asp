<?php

namespace App\Services;

use App\Repositories\ReserveReceiptSequence\ReserveReceiptSequenceRepository;

class ReserveReceiptSequenceService
{
    public function __construct(ReserveReceiptSequenceRepository $reserveReceiptSequenceRepository)
    {
        $this->reserveReceiptSequenceRepository = $reserveReceiptSequenceRepository;
    }

    /**
     * 番号を初期化
     */
    public function initCurrentNumber(int $agencyId, $date) : bool
    {
        return $this->reserveReceiptSequenceRepository->initCurrentNumber($agencyId, $date);
    }

    // 次の番号を取得
    public function getNextNumber(int $agencyId, $date): int
    {
        return $this->reserveReceiptSequenceRepository->getNextNumber($agencyId, $date);
    }
}
