<?php
namespace App\Repositories\ZoomApiKey;

use App\Models\ZoomApiKey;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ZoomApiKeyRepository implements ZoomApiKeyRepositoryInterface
{
    /**
    * @param object $agency
    */
    public function __construct(ZoomApiKey $zoomApiKey)
    {
        $this->zoomApiKey = $zoomApiKey;
    }

    // ランダムに一件取得
    public function findRandom() : ZoomApiKey
    {
        return $this->zoomApiKey->inRandomOrder()->first();
    }

}
