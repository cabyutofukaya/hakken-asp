<?php
namespace App\Repositories\ReserveCustomValue;

use App\Models\ReserveCustomValue;
use DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ReserveCustomValueRepository implements ReserveCustomValueRepositoryInterface
{
    /**
    * @param object $reserveCustomItem
    */
    public function __construct(ReserveCustomValue $reserveCustomValue)
    {
        $this->reserveCustomValue = $reserveCustomValue;
    }

    /**
     * 新規作成
     *
     * @param array $data
     */
    public function create(array $data) : ReserveCustomValue
    {
        return $this->reserveCustomValue->create($data);
    }

    /**
     * update or insert
     *
     * @return \Illuminate\Database\Eloquent\Model|static
     */
    public function updateOrCreate(array $attributes, array $values = []) : Model
    {
        return $this->reserveCustomValue->updateOrCreate(
            $attributes,
            $values
        );
    }

    /**
     * 項目更新
     */
    public function updateField(int $reserveCustomValueId, array $params) : Model
    {
        $this->reserveCustomValue->where('id', $reserveCustomValueId)->update($params);
        return $this->reserveCustomValue->findOrFail($reserveCustomValueId);
    }

    /**
     * バルクインサート
     *
     * @param array $data 保存配列
     */
    public function insert(array $data) : bool
    {
        $this->reserveCustomValue->insert($data);
        return true;
    }
}
