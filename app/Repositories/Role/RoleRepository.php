<?php
namespace App\Repositories\Role;

use App\Models\Role;
use Illuminate\Support\Collection;

class RoleRepository implements RoleRepositoryInterface
{
    /**
    * @param object $agency
    */
    public function __construct(Role $role)
    {
        $this->role = $role;
    }

    public function all(): Collection

    {
        return $this->role->all();
    }

    public function create(array $data): Role
    {
        return $this->role->create($data);
    }

    /**
     * 権限情報を取得
     * 
     * データがない場合は 404ステータス
     * 
     * @param int $id
     */
    public function find(int $id): Role
    {
        return $this->role->findOrFail($id);
    }

    public function update(int $id, array $data): Role
    {
        $role = $this->find($id);
        $role->fill($data)->save();
        return $role;
    }
    
    public function getIdByNameEn(string $nameEn) : ?int
    {
        return $this->role->where('name_en', $nameEn)->value('id');
    }

    public function delete(int $id): int
    {
        return $this->role->destroy($id);
    }
}
