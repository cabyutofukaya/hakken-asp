<?php

namespace App\Services;

use Lang;
use App\Models\AgencyRole;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use App\Repositories\AgencyRole\AgencyRoleRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class AgencyRoleService
{
    public function __construct(AgencyRoleRepository $agencyRoleRepository)
    {
        $this->agencyRoleRepository = $agencyRoleRepository;
    }

    public function find(int $id): AgencyRole
    {
        return $this->agencyRoleRepository->find($id);
    }

    /**
     * マスター管理者のIDを取得
     * 
     * @param int $agencyId
     */
    public function getMasterRoleId(int $agencyId): int
    {
        return $this->agencyRoleRepository->getMasterRoleId($agencyId);
    }

    /**
     * スタッフ登録許可数レンジ配列
     */
    public function all(): Collection
    {
        return $this->agencyRoleRepository->all();
    }

    public function create(array $data): AgencyRole
    {
        return $this->agencyRoleRepository->create($data);
    }

    public function update(int $id, array $data): AgencyRole
    {
        return $this->agencyRoleRepository->update($id, $data);
    }

    public function delete(int $id): int
    {
        return $this->agencyRoleRepository->delete($id);
    }

    /**
     * 権限一覧を取得（アカウント用）
     *
     * @param string $agencyAccount
     * @param int $limit
     * @param array $with
     */
    public function paginateByAgencyAccount(string $agencyAccount, array $params, int $limit, array $with=[], bool $getStaffCount = false) : LengthAwarePaginator
    {
        return $this->agencyRoleRepository->paginateByAgencyAccount($agencyAccount, $params, $limit, $with, $getStaffCount);
    }

    /**
     * 当該会社のユーザー権限一覧を取得
     */
    public function getNamesByAgencyId(int $agencyId) : array
    {
        return $this->agencyRoleRepository->getWhere(['agency_id' => $agencyId])->pluck("name", "id")->toArray();
    }

    /**
     * 当該会社のユーザー権限ID一覧を取得
     */
    public function getIdsByAgencyId(int $agencyId) : array
    {
        return $this->agencyRoleRepository->getWhere(['agency_id' => $agencyId])->pluck("id")->toArray();
    }
}
