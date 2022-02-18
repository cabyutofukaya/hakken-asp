<?php

namespace App\Services;

use App\Models\Direction;
use Illuminate\Support\Collection;
use App\Repositories\Direction\DirectionRepository;
use App\Repositories\Agency\AgencyRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class DirectionService
{
    public function __construct(AgencyRepository $agencyRepository, DirectionRepository $directionRepository)
    {
        $this->agencyRepository = $agencyRepository;
        $this->directionRepository = $directionRepository;
    }

    /**
     * 該当IDを一件取得
     *
     * @param int $id ID
     * @param array $select 取得カラム
     */
    public function find(int $id, array $select=[]) : ?Direction
    {
        return $this->directionRepository->find($id, $select);
    }

    /**
     * 当該UUIDを一件取得
     * 
     * @param string $uuid
     */
    public function findByUuid(string $uuid, array $select=[]) : ?Direction
    {
        return $this->directionRepository->findByUuid($uuid, $select);
    }

    /**
     * 方向一覧を取得（アカウント用）
     *
     * @param string $account
     * @param int $limit
     * @param array $with
     */
    public function paginateByAgencyAccount(string $account, array $params, int $limit, $select=[]) : LengthAwarePaginator
    {
        $agencyId = $this->agencyRepository->getIdByAccount($account);
        return $this->directionRepository->paginateByAgencyId($agencyId, $params, $limit, $select);
    }

    public function create(array $data): Direction
    {
        $data['uuid'] = Str::uuid(); // 新規登録時のみuuidをセット
        return $this->directionRepository->create($data);
    }

    public function update(int $id, array $data): Direction
    {
        // 方面コードは更新不可なので一応、配列に入っていたらカットしておく
        if (isset($data['code'])) {
            unset($data['code']);
        }
        return $this->directionRepository->update($id, $data);
    }

    /**
     * 更新
     * 対象レコードをUUIDで検索
     */
    public function updateByUuid(string $uuid, array $data) : Direction
    {
        $direction = $this->directionRepository->findByUuid($uuid, []);
        return $this->update($direction->id, $data);
    }

    /**
     * 削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
     */
    public function delete(int $id, bool $isSoftDelete=true): bool
    {
        return $this->directionRepository->delete($id, $isSoftDelete);
    }

    /**
     * 削除
     * uuidで対象を検索
     */
    public function deleteByUuid(string $uuid, bool $isSoftDelete=true) : bool
    {
        $direction = $this->directionRepository->findByUuid($uuid, []);
        return $this->directionRepository->delete($direction->id, $isSoftDelete);
    }

    /**
     * 当該会社に紐づく名称一覧を取得
     * 
     * @return array
     */
    public function getNameListByAgencyAccount(string $account) : array
    {
        $agencyId = $this->agencyRepository->getIdByAccount($account);

        return $this->directionRepository->getWhere(['agency_id' => $agencyId], ['id', 'name'])->pluck("name", "id")->toArray();
    }
}
