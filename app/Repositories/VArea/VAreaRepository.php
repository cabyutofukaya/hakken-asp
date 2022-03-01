<?php
namespace App\Repositories\VArea;

use App\Models\VArea;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class VAreaRepository implements VAreaRepositoryInterface
{
    /**
    * @param object $agency
    */
    public function __construct(VArea $vArea)
    {
        $this->vArea = $vArea;
    }

    /**
     * 当該IDを取得
     * 
     * @param int $id
     */
    public function find(int $id, array $select = []): ?VArea
    {
        return $select ? $this->vArea->select($select)->find($id) : $this->vArea->first($id);
    }

    /**
     * 当該UUIDを取得
     *
     * @param int $id
     */
    public function findByUuid(string $uuid, array $select=[]) : ?VArea
    {
        $query = $this->vArea;
        $query = $select ? $query->select($select) : $query;
        return $query->where('uuid', $uuid)->first();
    }

    /**
     * ページネーション で取得（for 会社ID）
     *
     * @var $limit
     * @return object
     */
    public function paginateByAgencyId(int $agencyId, array $params, int $limit, array $with, array $select) : LengthAwarePaginator
    {
        $query = $this->vArea;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        
        foreach ($params as $key => $val) {
            if (is_empty($val)) {
                continue;
            }
            $query = $query->where($key, 'like', "%$val%");
        }

        return $query->where(function ($q) use ($agencyId) {
            $q->where("v_areas.agency_id", $agencyId)
                ->orWhere("v_areas.agency_id", config('consts.const.MASTER_AGENCY_ID')); // master_areasテーブルの値も必ず付与する
        })->sortable()->paginate($limit);
        // sortableする際にagency_idがリレーション先のテーブルにも存在するのでエラー回避のために明示的にagency_idを指定する

    }

    /**
     * 当該会社IDに紐づく国・地域一覧を取得
     * 自社登録データ + master_areasデータ
     */
    public function getByAgencyAccount(int $agencyId, array $select = []) : Collection
    {
        $query = $this->vArea;
        $query = $select ? $query->select($select) : $query;

        return $query->where(function ($q) use ($agencyId) {
            $q->where("agency_id", $agencyId)
                ->orWhere("agency_id", config('consts.const.MASTER_AGENCY_ID')); // master_areasテーブルの値も必ず付与する
        })->get();

    }

    /**
     * 名称検索
     */
    public function search(int $agencyId, string $str, array $with=[], array $select=[], $limit=null) : Collection
    {
        $query = $this->vArea;
        
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
