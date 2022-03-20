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
}
