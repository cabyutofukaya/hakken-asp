<?php
namespace App\Repositories\VDirection;

use App\Models\VDirection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class VDirectionRepository implements VDirectionRepositoryInterface
{
    /**
    * @param object $agency
    */
    public function __construct(VDirection $vDirection)
    {
        $this->vDirection = $vDirection;
    }

    /**
     * 当該UUIDを取得
     *
     * @param int $id
     */
    public function findByUuid(string $uuid, array $select=[]) : ?VDirection
    {
        $query = $this->vDirection;
        $query = $select ? $query->select($select) : $query;
        return $query->where('uuid', $uuid)->first();
    }

    /**
     * ページネーションで取得（for 会社ID）
     *
     * @var $limit
     * @return object
     */
    public function paginateByAgencyId(int $agencyId, array $params, int $limit, array $select) : LengthAwarePaginator
    {
        $query = $this->vDirection;
        $query = $select ? $query->select($select) : $query;
        foreach ($params as $key => $val) {
            if (is_empty($val)) {
                continue;
            }
            $query = $query->where($key, 'like', "%$val%");
        }

        return $query->where(function ($q) use ($agencyId) {
            $q->where("agency_id", $agencyId)
                ->orWhere("agency_id", config('consts.const.MASTER_AGENCY_ID')); // master_directionsテーブルの値も必ず付与する
        })->sortable()->paginate($limit);
    }

    /**
     * 当該会社IDに紐づく方向一覧を取得
     * 自社登録データ + master_directionsデータ
     */
    public function getByAgencyAccount(int $agencyId, array $select = []) : Collection
    {
        $query = $this->vDirection;
        $query = $select ? $query->select($select) : $query;

        return $query->where(function ($q) use ($agencyId) {
            $q->where("agency_id", $agencyId)
                ->orWhere("agency_id", config('consts.const.MASTER_AGENCY_ID')); // master_directionsテーブルの値も必ず付与する
        })->get();

    }

    /**
     * 名称検索
     */
    public function search(int $agencyId, string $str, array $with=[], array $select=[], $limit=null) : Collection
    {
        $query = $this->vDirection;
        
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;

        $query = $query->where(function ($q) use ($agencyId) {
            $q->where("agency_id", $agencyId)
                ->orWhere("agency_id", config('consts.const.MASTER_AGENCY_ID')); // master_areasテーブルの値も必ず付与する
        })->where(function($q) use ($str){
            $q->where("name", 'like', "%$str%")
                ->orWhere("code", 'like', "%$str%");
        });

        return !is_null($limit) ? $query->take($limit)->get() : $query->get();

    }

}
