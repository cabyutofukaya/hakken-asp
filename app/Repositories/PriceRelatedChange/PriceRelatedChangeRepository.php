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

    /**
     * 値を取得
     */
    public function findWhereValue(array $where, string $value)
    {
        $query = $this->priceRelatedChange;
        foreach ($where as $k => $v) {
            $query = $query->where($k, $v);
        }
        return $query->value($value);
    }
}
