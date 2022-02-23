<?php
namespace App\Repositories\BaseWebUser;

use App\Models\BaseWebUser;
use Illuminate\Pagination\LengthAwarePaginator;

class BaseWebUserRepository implements BaseWebUserRepositoryInterface
{
    /**
    * @param object $baseWebUser
    */
    public function __construct(BaseWebUser $baseWebUser)
    {
        $this->baseWebUser = $baseWebUser;
    }

    /**
     * @param bool $getDeleted 論理削除を含めるか
     */
    public function find(int $id, array $with=[], array $select=[], bool $getDeleted = false): BaseWebUser
    {
        $query = $this->baseWebUser->definitive(); // 本登録ユーザーのみ
        $query = $select ? $query->select($select) : $query;
        $query = $with ? $query->with($with) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        return $query->findOrFail($id);
    }

    public function update(int $id, array $data): BaseWebUser
    {
        $baseWebUser = $this->baseWebUser->find($id);
        $baseWebUser->fill($data)->save();
        return $baseWebUser;
    }

    public function updateField(int $id, array $params) : bool
    {
        $this->baseWebUser->where('id', $id)->update($params);
        return true;
    }

    /**
     * ページネーションで取得
     */
    public function paginate(array $params, int $limit, array $with=[], array $select = []) : LengthAwarePaginator
    {
        $query = $this->baseWebUser->definitive(); // 本登録ユーザーのみ

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
            $this->baseWebUser->destroy($id);
        } else {
            $this->baseWebUser->find($id)->forceDelete();
        }
        return true;
    }
}
