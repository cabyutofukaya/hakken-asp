<?php
namespace App\Repositories\PriceRelatedChange;

use App\Models\PriceRelatedChange;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PriceRelatedChangeRepository implements PriceRelatedChangeRepositoryInterface
{
    /**
    * @param object $priceRelatedChange
    */
    public function __construct(PriceRelatedChange $priceRelatedChange)
    {
        $this->priceRelatedChange = $priceRelatedChange;
    }

    /**
     * アップサート
     */
    public function updateOrCreate(array $attributes, array $values = []) : PriceRelatedChange
    {
        return $this->priceRelatedChange->updateOrCreate(
            $attributes,
            $values
        );
    }
}
