<?php
namespace App\Repositories\MasterDirection;

use Illuminate\Support\Collection;
use App\Models\MasterDirection;
use Illuminate\Database\Eloquent\Model;

class MasterDirectionRepository implements MasterDirectionRepositoryInterface
{
    /**
    * @param MasterDirection $masterDirection
    */
    public function __construct(MasterDirection $masterDirection)
    {
        $this->masterDirection = $masterDirection;
    }

    /**
     * 方面コードからIDを取得
     */
    public function getIdByCode(string $code) : ?int
    {
        return $this->masterDirection->where('code', $code)->value("id");
    }

    /**
     * 方面コードからUUIDを取得
     */
    public function getUuidByCode(string $code) : ?string
    {
        return $this->masterDirection->where('code', $code)->value("uuid");
    }

    /**
     * 検索して全件取得
     *
     */
    public function getWhere(array $where, array $with=[], array $select=[]) : Collection
    {
        $query = $this->masterDirection;
        
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;

        foreach ($where as $key => $val) {
            if (is_empty($val)) {
                continue;
            }
            $query = $query->where($key, $val);
        }
        return $query->get();
    }

    /**
     * update or insert
     */
    public function updateOrCreate(array $attributes, array $values = []) : Model
    {
        return $this->masterDirection->updateOrCreate(
            $attributes,
            $values
        );
    }

    /**
     * 条件で削除
     *
     * @param array $where 条件パラメータ
     */
    public function deleteExceptionGenKey(string $genKey): bool
    {
        foreach ($this->masterDirection->where('gen_key', '!=', $genKey)->get() as $row) {
            $row->delete(); // 1行ずつ削除しないと、Modelのstatic::deleting が呼ばれないようなのでforeachで1行ずつ処理
        }
        return true;
    }
}
