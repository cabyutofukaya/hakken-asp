<?php
namespace App\Repositories\WebModelcourse;

use App\Models\WebModelcourse;
use Illuminate\Pagination\LengthAwarePaginator;

class WebModelcourseRepository implements WebModelcourseRepositoryInterface
{
    /**
    * @param object $webProfileTag
    */
    public function __construct(WebModelcourse $webModelcourse)
    {
        $this->webModelcourse = $webModelcourse;
    }

    public function find(int $id, array $with=[], array $select=[], bool $getDeleted = false): WebModelcourse
    {
        $query = $this->webModelcourse;
        
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        return $query->findOrFail($id);
    }

    /**
     * アップサート
     */
    public function updateOrCreate(array $attributes, array $values = []) : WebModelcourse
    {
        return $this->webModelcourse->updateOrCreate(
            $attributes,
            $values
        );
    }
    
    /**
     * ページネーション で取得（for 会社ID）
     *
     * @var $limit
     * @return object
     */
    public function paginateByAgencyId(int $agencyId, array $params, int $limit, array $with, array $select) : LengthAwarePaginator
    {
        $query = $this->webModelcourse;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        
        foreach ($params as $key => $val) {
            if (is_empty($val)) {
                continue;
            }
            $query = $query->where($key, 'like', "%$val%");
        }

        return $query->where('web_modelcourses.agency_id', $agencyId)->sortable()->paginate($limit); // sortableする際にagency_idがリレーション先のテーブルにも存在するのでエラー回避のために明示的にagency_idを指定する
    }

    /**
     * 検索して1件取得
     */
    public function findWhere(array $where, array $with=[], array $select=[], bool $getDeleted = false) : ?WebModelcourse
    {
        $query = $this->webModelcourse;
        
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        foreach ($where as $key => $val) {
            if (is_empty($val)) {
                continue;
            }
            $query = $query->where($key, $val);
        }
        return $query->first();
    }

    /**
     * 作成済みのコース数を取得
     *
     * @param bool $includDeleted 論理削除を含む場合はtrue
     */
    public function getCount(int $agencyId, bool $includDeleted = true) : int
    {
        $query = $this->webModelcourse;
        $query = $includDeleted ? $query->withTrashed() : $query;

        return $query->where('agency_id', $agencyId)->count();
    }

    /**
     * 当該作成者の有効コース数を取得
     */
    public function getValidCountByAuthorId(int $authorId) : int
    {
        return $this->webModelcourse
            ->where('author_id', $authorId)
            ->where('show', true) // 表示フラグON
            ->count();
    }

    public function updateFields(int $id, array $params) : bool
    {
        $this->webModelcourse->where('id', $id)->update($params);
        return true;
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
            $this->webModelcourse->destroy($id);
        } else {
            $this->find($id)->forceDelete();
        }
        return true;
    }
}
