<?php
namespace App\Repositories\WebProfile;

use App\Models\WebProfile;

class WebProfileRepository implements WebProfileRepositoryInterface
{
    /**
    * @param object $webProfile
    */
    public function __construct(WebProfile $webProfile)
    {
        $this->webProfile = $webProfile;
    }

    /**
     * @param bool $getDeleted 論理削除を含めるか
     */
    public function find(int $id, array $with=[], array $select=[], bool $getDeleted = false): WebProfile
    {
        $query = $this->webProfile;
        $query = $select ? $query->select($select) : $query;
        $query = $with ? $query->with($with) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        return $query->findOrFail($id);
    }

    /**
     * 検索して1件取得
     */
    public function findWhere(array $where, array $with=[], array $select=[], bool $getDeleted = false) : ?WebProfile
    {
        $query = $this->webProfile;
        
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
     * アップサート
     */
    public function updateOrCreate(array $attributes, array $values = []) : WebProfile
    {
        return $this->webProfile->updateOrCreate(
            $attributes,
            $values
        );
    }
    
    public function updateFields(int $id, array $params) : bool
    {
        $this->webProfile->where('id', $id)->update($params);
        return true;
    }
}
