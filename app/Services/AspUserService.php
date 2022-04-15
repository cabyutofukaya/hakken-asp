<?php

namespace App\Services;

use App\Models\AspUser;
use App\Repositories\AspUser\AspUserRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class AspUserService
{
    public function __construct(
        AspUserRepository $aspUserRepository
    ) {
        $this->aspUserRepository = $aspUserRepository;
    }

    public function create(array $data) : AspUser
    {
        return $this->aspUserRepository->create($data);
    }

    /**
     * バルクインサート
     */
    public function insert(array $rows) : bool
    {
        $this->aspUserRepository->insert($rows);
        return true;
    }

    /**
     * バルクアップデート
     *
     * @param array $params
     */
    public function updateBulk(array $params, string $id = "id") : bool
    {
        return $this->aspUserRepository->updateBulk($params, $id);
    }

    /**
     * 検索してID一覧を取得
     */
    public function getWhereIds(array $where) : array
    {
        return $this->aspUserRepository->getWhereIds($where);
    }
}
