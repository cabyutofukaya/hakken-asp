<?php

namespace App\Services;

use App\Models\PriceRelatedChange;
use App\Repositories\PriceRelatedChange\PriceRelatedChangeRepository;
use Illuminate\Http\Request;

class PriceRelatedChangeService
{
    public function __construct(PriceRelatedChangeRepository $priceRelatedChangeRepository)
    {
        $this->priceRelatedChangeRepository = $priceRelatedChangeRepository;
    }

    public function upsert(int $reserveId, string $updatedAt) : PriceRelatedChange
    {
        return $this->priceRelatedChangeRepository->updateOrCreate(
            ['reserve_id' => $reserveId],
            ['change_at' => $updatedAt]
        );
    }

    /**
     * 記録日時を取得
     *
     * @param int $reserveId 予約ID
     */
    public function getChangeAt(int $reserveId) : ?string
    {
        $changeAt = $this->priceRelatedChangeRepository->findWhereValue(['reserve_id' => $reserveId], "change_at");
        return $changeAt ? $changeAt->format('Y-m-d H:i:s') : null;
    }
}
