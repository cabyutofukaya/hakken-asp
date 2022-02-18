<?php

namespace App\Services;

/**
 * 予約・見積の共通処理を定義するインターフェイス
 */
interface ReserveEstimateInterface
{
    public function updateFields(int $reserveId, array $params) : bool;

    public function createReserveNumber($agencyId) : string;
}
