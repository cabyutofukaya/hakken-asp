<?php

namespace App\Services;

use App\Repositories\ReserveInvoiceSequence\ReserveInvoiceSequenceRepository;

class ReserveInvoiceSequenceService
{
    public function __construct(ReserveInvoiceSequenceRepository $reserveInvoiceSequenceRepository)
    {
        $this->reserveInvoiceSequenceRepository = $reserveInvoiceSequenceRepository;
    }

    /**
     * 番号を初期化
     */
    public function initCurrentNumber(int $agencyId, $date) : bool
    {
        return $this->reserveInvoiceSequenceRepository->initCurrentNumber($agencyId, $date);
    }

    // 次の番号を取得
    public function getNextNumber(int $agencyId, $date): int
    {
        return $this->reserveInvoiceSequenceRepository->getNextNumber($agencyId, $date);
    }

}
