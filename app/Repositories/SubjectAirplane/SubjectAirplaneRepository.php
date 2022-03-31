<?php
namespace App\Repositories\SubjectAirplane;

use App\Models\SubjectAirplane;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SubjectAirplaneRepository implements SubjectAirplaneRepositoryInterface
{
    public function __construct(SubjectAirplane $subjectAirplane)
    {
        $this->subjectAirplane = $subjectAirplane;
    }

    /**
     * 当該IDを取得
     *
     * データがない場合は 404ステータス
     *
     * @param int $id
     */
    public function find(int $id, array $select = []): SubjectAirplane
    {
        return $select ? $this->subjectAirplane->select($select)->findOrFail($id) : $this->subjectAirplane->findOrFail($id);
    }

    /**
     * 検索して1件取得
     */
    public function findWhere(array $where, array $with=[], array $select=[]) : ?SubjectAirplane
    {
        $query = $this->subjectAirplane;
        
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;

        foreach ($where as $key => $val) {
            if (is_empty($val)) {
                continue;
            }
            $query = $query->where($key, $val);
        }
        return $query->first();
    }

    /**
     * ページネーション で取得（for 会社ID）
     *
     * @var $limit
     * @return object
     */
    public function paginateByAgencyId(int $agencyId, array $params, int $limit, array $with, array $select) : LengthAwarePaginator
    {
        $query = $this->subjectAirplane;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;

        foreach ($params as $key => $val) {
            if (is_empty($val)) {
                continue;
            }

            if (strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_PREFIX')) === 0) {
                // カスタム項目
                $query = $query->whereHas('v_subject_airplane_custom_values', function ($q) use ($key, $val) {
                    $q->where('key', $key)->where('val', 'like', "%$val%");
                });
            } else {
                $query = $query->where($key, 'like', "%$val%");
            }
        }


        return $query->where('subject_airplanes.agency_id', $agencyId)->sortable()->paginate($limit); // sortableする際にagency_idがリレーション先のテーブルにも存在するのでエラー回避のために明示的にagency_idを指定する
    }

    /**
     * 作成
     */
    public function create(array $data): SubjectAirplane
    {
        return $this->subjectAirplane->create($data);
    }

    /**
     * 更新
     */
    public function update(int $id, array $data): SubjectAirplane
    {
        $subjectAirplane = $this->find($id);
        $subjectAirplane->fill($data)->save();
        return $subjectAirplane;
    }

    /**
     * 当該カラムをアップデート
     */
    public function updateField(int $subjectAirplaneId, array $params) : bool
    {
        $this->subjectAirplane->where('id', $subjectAirplaneId)->update($params);
        return true;

        // $subjectAirplane = $this->subjectAirplane->findOrFail($subjectAirplaneId);
        // foreach ($params as $k => $v) {
        //     $subjectAirplane->{$k} = $v; // プロパティに値をセット
        // }
        // $subjectAirplane->save();
        // return 1;
    }

    /**
     * 名称検索
     */
    public function search(int $agencyId, string $str, array $with=[], array $select=[], $limit=null, $order = 'id', $direction = 'asc') : Collection
    {
        $query = $this->subjectAirplane;
        
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;

        $query = $query->where("agency_id", $agencyId);
        $query->where(function($q) use ($str) {
            $q->where("name", 'like', "%$str%")
                ->orWhere("code", 'like', "%$str%");
        });

        return !is_null($limit) ? $query->take($limit)->orderBy($order, $direction)->get() : $query->orderBy($order, $direction)->get();
    }

    /**
     * 削除
     * 
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue
     * @return boolean
     */
    public function delete(int $id, bool $isSoftDelete): bool
    {
        if ($isSoftDelete) {
            $this->subjectAirplane->destroy($id);
        } else {
            $this->find($id)->forceDelete();
        }
        return true;
    }
}
