<?php
namespace App\Repositories\MasterArea;

use App\Models\MasterArea;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

class MasterAreaRepository implements MasterAreaRepositoryInterface
{
    /**
    * @param MasterArea $masterArea
    */
    public function __construct(MasterArea $masterArea)
    {
        $this->masterArea = $masterArea;
    }

    /**
     * コードからIDを取得
     */
    public function getIdByCode(string $code) : ?int
    {
        return $this->masterArea->where('code', $code)->value("id");
    }

    public function getDefaultList() : Collection
    {
        return $this->masterArea->select('uuid','code','name')->where('is_default', true)->get();
    }

    /**
     * update or insert
     */
    public function updateOrCreate(array $attributes, array $values = []) : Model
    {
        return $this->masterArea->updateOrCreate(
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
        foreach ($this->masterArea->where('gen_key', '!=', $genKey)->get() as $row) {
            $row->delete(); // 1行ずつ削除しないと、Modelのstatic::deleting が呼ばれないようなのでforeachで1行ずつ処理
        }
        return true;
    }
}
