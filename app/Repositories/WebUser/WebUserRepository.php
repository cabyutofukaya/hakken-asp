<?php
namespace App\Repositories\WebUser;

use App\Models\WebUser;
use Illuminate\Pagination\LengthAwarePaginator;

class WebUserRepository implements WebUserRepositoryInterface
{
    /**
    * @param object $webUser
    */
    public function __construct(WebUser $webUser)
    {
        $this->webUser = $webUser;
    }

    /**
     * @param bool $getDeleted 論理削除を含めるか
     */
    public function find(int $id, array $with=[], array $select=[], bool $getDeleted = false): WebUser
    {
        $query = $this->webUser->definitive(); // 本登録ユーザーのみ
        $query = $select ? $query->select($select) : $query;
        $query = $with ? $query->with($with) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        return $query->findOrFail($id);
    }

    public function update(int $id, array $data): WebUser
    {
        $webUser = $this->webUser->find($id);
        $webUser->fill($data)->save();
        return $webUser;
    }

    public function updateField(int $id, array $params) : bool
    {
        $this->webUser->where('id', $id)->update($params);
        return true;
    }

    /**
     * ページネーションで取得
     */
    public function paginate(array $params, int $limit, array $with=[], array $select = []) : LengthAwarePaginator
    {
        $query = $this->webUser->definitive(); // 本登録ユーザーのみ

        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        
        foreach ($params as $key => $val) {
            if (is_empty($val)) {
                continue;
            }
            $query = $query->where($key, 'like', "%$val%");
        }

        return $query->sortable(['user_number' => 'desc'])->paginate($limit);
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
            $this->webUser->destroy($id);
        } else {
            $this->webUser->find($id)->forceDelete();
        }
        return true;
    }
}
