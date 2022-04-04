<?php

namespace App\Traits;

/**
 * 料金関連の変更日時をチェックするtrait
 */
trait PriceRelatedChangeTrait
{
    /**
     * 料金更新日時をチェック。保存済みの日時よりも新しければtrue
     *
     * @param int $reserveId 予約ID
     * @param string $changeAt 更新日時
     */
    public function checkPriceUpdatedAt(int $reserveId, ?string $changeAt)
    {
        return $this->priceRelatedChangeService->getChangeAt($reserveId) <= $changeAt;
    }
}
