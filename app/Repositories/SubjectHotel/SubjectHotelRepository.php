<?php
namespace App\Repositories\SubjectHotel;

use App\Models\SubjectHotel;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SubjectHotelRepository implements SubjectHotelRepositoryInterface
{
    public function __construct(SubjectHotel $subjectHotel)
    {
        $this->subjectHotel = $subjectHotel;
    }

    /**
     * 当該IDを取得
     *
     * データがない場合は 404ステータス
     *
     * @param int $id
     */
    public function find(int $id, array $select = []): SubjectHotel
    {
        return $select ? $this->subjectHotel->select($select)->findOrFail($id) : $this->subjectHotel->findOrFail($id);
    }

    /**
     * ページネーション で取得（for 会社ID）
     *
     * @var $limit
     * @return object
     */
    public function paginateByAgencyId(int $agencyId, array $params, int $limit, array $with, array $select) : LengthAwarePaginator
    {
        $query = $this->subjectHotel;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;

        foreach ($params as $key => $val) {
            if (is_empty($val)) {
                continue;
            }

            if (strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_PREFIX')) === 0) {
                // カスタム項目
                $query = $query->whereHas('v_subject_hotel_custom_values', function ($q) use ($key, $val) {
                    $q->where('key', $key)->where('val', 'like', "%$val%");
                });
            } else {
                $query = $query->where($key, 'like', "%$val%");
            }
        }


        return $query->where('subject_hotels.agency_id', $agencyId)->sortable()->paginate($limit); // sortableする際にagency_idがリレーション先のテーブルにも存在するのでエラー回避のために明示的にagency_idを指定する
    }

    /**
     * 作成
     */
    public function create(array $data): SubjectHotel
    {
        return $this->subjectHotel->create($data);
    }

    /**
     * 更新
     */
    public function update(int $id, array $data): SubjectHotel
    {
        $subjectHotel = $this->find($id);
        $subjectHotel->fill($data)->save();
        return $subjectHotel;
    }

    /**
     * 当該カラムをアップデート
     */
    public function updateField(int $subjectHotelId, array $params) : int
    {
        $this->subjectHotel->where('id', $subjectHotelId)->update($params);
        return 1;

        // $subjectHotel = $this->subjectHotel->findOrFail($subjectHotelId);
        // foreach ($params as $k => $v) {
        //     $subjectHotel->{$k} = $v; // プロパティに値をセット
        // }
        // $subjectHotel->save();
        // return 1;
    }

    /**
     * 名称検索
     */
    public function search(int $agencyId, string $str, array $with=[], array $select=[], $limit=null, $order = 'id', $direction = 'asc') : Collection
    {
        $query = $this->subjectHotel;
        
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;

        $query = $query->where("agency_id", $agencyId);
        $query->where(function ($q) use ($str) {
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
            $this->subjectHotel->destroy($id);
        } else {
            $this->find($id)->forceDelete();
        }
        return true;
    }
}
