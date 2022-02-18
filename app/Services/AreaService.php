<?php

namespace App\Services;

use App\Models\Area;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\Area\AreaRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AreaService
{
    public function __construct(AgencyRepository $agencyRepository, AreaRepository $areaRepository)
    {
        $this->agencyRepository = $agencyRepository;
        $this->areaRepository = $areaRepository;
    }

    /**
     * 該当IDを一件取得
     *
     * @param int $id ID
     * @param array $select 取得カラム
     */
    public function find(int $id, array $select=[]) : ?Area
    {
        return $this->areaRepository->find($id, $select);
    }

    /**
     * 当該UUIDを一件取得
     * 
     * @param string $uuid
     */
    public function findByUuid(string $uuid, array $select=[]) : ?Area
    {
        return $this->areaRepository->findByUuid($uuid, $select);
    }

    /**
     * 一覧を取得（アカウント用）
     *
     * @param string $account
     * @param int $limit
     * @param array $with
     */
    public function paginateByAgencyAccount(string $account, array $params, int $limit, array $with = [], array $select=[]) : LengthAwarePaginator
    {
        $agencyId = $this->agencyRepository->getIdByAccount($account);
        return $this->areaRepository->paginateByAgencyId($agencyId, $params, $limit, $with, $select);
    }

    public function create(array $data): Area
    {
        $data['uuid'] = Str::uuid(); // 新規登録時のみuuidをセット
        return $this->areaRepository->create($data);
    }

    public function update(int $id, array $data): Area
    {
        // 国・地域コードは更新不可なので一応、配列に入っていたらカットしておく
        if (isset($data['code'])) {
            unset($data['code']);
        }
        return $this->areaRepository->update($id, $data);
    }

    /**
     * 更新
     * 対象レコードをUUIDで検索
     */
    public function updateByUuid(string $uuid, array $data) : Area
    {
        $area = $this->areaRepository->findByUuid($uuid, []);
        return $this->update($area->id, $data);
    }

    /**
     * 削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
     */
    public function delete(int $id, bool $isSoftDelete=true): bool
    {
        return $this->areaRepository->delete($id, $isSoftDelete);
    }

    /**
     * 削除
     * uuidで対象を検索
     */
    public function deleteByUuid(string $uuid, bool $isSoftDelete=true) : bool
    {
        $area = $this->areaRepository->findByUuid($uuid, []);
        return $this->areaRepository->delete($area->id, $isSoftDelete);
    }

    /**
     * 当該会社に紐づく名称一覧を取得
     * 
     * @return array
     */
    public function getNameListByAgencyAccount(string $account) : array
    {
        $agencyId = $this->agencyRepository->getIdByAccount($account);

        return $this->areaRepository->getWhere(['agency_id' => $agencyId], ['id', 'name'])->pluck("name", "id")->toArray();
    }
}
