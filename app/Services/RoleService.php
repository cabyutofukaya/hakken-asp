<?php

namespace App\Services;

use Lang;
use App\Models\Role;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use App\Repositories\Role\RoleRepository;

class RoleService
{
    private $roleRepository;

    public function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    public function find(int $id): Role
    {
        return $this->roleRepository->find($id);
    }

    /**
     * スタッフ登録許可数レンジ配列
     */
    public function all(): Collection
    {
        return $this->roleRepository->all();
    }

    public function create(array $data): Role
    {
        $data['authority'] = json_encode(Arr::get($data, 'authority', []));
        return $this->roleRepository->create($data);
    }

    public function update(int $id, array $data): Role
    {
        $data['authority'] = json_encode(Arr::get($data, 'authority', []));
        return $this->roleRepository->update($id, $data);
    }

    public function delete(int $id): int
    {
        return $this->roleRepository->delete($id);
    }

    /**
     * 任意のrole名のIDを取得
     * 
     * @param string $nameEn name_en
     * @return int
     */
    public function getIdByNameEn(string $nameEn) : ?int
    {
        return $this->roleRepository->getIdByNameEn($nameEn);
    }

    /**
     * 権限詳細項目一覧
     */
    public function getRoleItems()
    {
        $targets = Lang::get('values.roles.targets');
        $actions = Lang::get('values.roles.actions');

        // 対象によっては未実装の機能もあるが、とりあえず全てのアクションを対象にリストを作る
        foreach (config("consts.roles.TARGETS_LIST") as $targetKey => $targetVal) {
            $row = array();
            $row['target'] = $targetVal;
            $row['label'] = Arr::get($targets, $targetKey);

            foreach (config("consts.roles.ACTIONS_LIST") as $actionKey => $actionVal) {
                $item = array();
                $item['action'] = $actionVal;
                $item['label'] = Arr::get($actions, $actionKey);

                $row['items'][] = $item;
            }

            $authority[] = $row;
        }

        return $authority;
    }
}
