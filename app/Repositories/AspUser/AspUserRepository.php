<?php
namespace App\Repositories\AspUser;

use App\Models\AspUser;
use Illuminate\Pagination\LengthAwarePaginator;

class AspUserRepository implements AspUserRepositoryInterface
{
    /**
    * @param object $aspUser
    */
    public function __construct(AspUser $aspUser)
    {
        $this->aspUser = $aspUser;
    }

    public function create(array $data) : AspUser
    {
        return $this->aspUser->create($data);
    }

    /**
     * バルクインサート
     */
    public function insert(array $rows) : bool
    {
        $this->aspUser->insert($rows);
        return true;
    }

    /**
     * バルクアップデート
     *
     * @param array $params
     */
    public function updateBulk(array $params, string $id) : bool
    {
        $this->aspUser->updateBulk($params, $id);
        return true;
    }

    /**
     * 検索してID一覧を取得
     */
    public function getWhereIds(array $where) : array
    {
        $query = $this->aspUser->select(['id']);
        foreach ($where as $key => $val) {
            if (is_empty($val)) {
                continue;
            }
            $query = $query->where($key, $val);
        }
        return $query->pluck('id')->toArray();
    }
}
