<?php
namespace App\Repositories\Staff;

use App\Models\Staff;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class StaffRepository implements StaffRepositoryInterface
{
    protected $staff;

    /**
    * @param object $staff
    */
    public function __construct(Staff $staff)
    {
        $this->staff = $staff;
    }

    /**
     * @param bool $getDeleted 論理削除を含めるか
     */
    public function find(int $id, array $with=[], array $select=[], bool $getDeleted = false): ?Staff
    {
        $query = $this->staff;
        $query = $select ? $query->select($select) : $query;
        $query = $with ? $query->with($with) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        return $query->find($id);
    }

    public function findWhere(array $where, array $with=[], array $select=[]) : ?Staff
    {
        $query = $this->staff;
        $query = $select ? $query->select($select) : $query;
        $query = $with ? $query->with($with) : $query;

        foreach($where as $key => $val)
        {
            $query = $query->where($key, $val);
        }
        return $query->first();
    }

    /**
     * ページネーション で取得（ID用）
     *
     * @var $limit
     * @return object
     */
    public function paginateByAgencyId(int $agencyId, array $params, int $limit, array $with, array $select) : LengthAwarePaginator
    {
        $query = $this->staff;
        $query = $select ? $query->select($select) : $query;
        $query = $with ? $query->with($with) : $query;

        foreach ($params as $key => $val) {
            if (is_empty($val)) {
                continue;
            }

            if (strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_PREFIX')) === 0) { // カスタム項目
                $query = $query->whereHas('v_staff_custom_values', function ($q) use ($key, $val) {
                    $q->where('key', $key)->where('val', 'like', "%$val%");
                });
            } else {
                $query = $query->where($key, 'like', "%$val%");
            }
        }
        return $query->where('staffs.agency_id', $agencyId)->sortable()->paginate($limit);
        // return $query->orderBy($sort, $direction)->paginate($limit);
    }

    public function create(array $data) : Staff
    {
        return $this->staff->create($data);
    }

    public function update(int $id, array $data): Staff
    {
        $staff = $this->find($id);
        $staff->fill($data)->save();
        return $staff;
    }

    public function updateFields(int $staffId, array $params) : bool
    {
        $this->staff->where('id', $staffId)->update($params);
        return true;

        // $staff = $this->staff->findOrFail($staffId);
        // foreach ($params as $k => $v) {
        //     $staff->{$k} = $v; // プロパティに値をセット
        // }
        // $staff->save();
        // return 1;
    }
    
    public function delete(int $id): int
    {
        return $this->staff->destroy($id);
    }

    public function countByAgencyId(int $agencyId): int
    {
        return $this->staff->where('agency_id', $agencyId)->count();
    }

    public function getCountByAgencyRoleId(int $agencyRoleId): int
    {
        return $this->staff->where('agency_role_id', $agencyRoleId)->count();
    }

    public function getWhere(array $where, array $select = [], bool $getDeleted = false): Collection
    {
        $query = $this->staff;
        $query = $select ? $query->select($select) : $query;

        foreach ($where as $key => $val) {
            $query = $query->where($key, $val);
        }
        
        $query = $getDeleted ? $query->withTrashed() : $query;
        return $query->get();
    }

    /**
     * 条件で更新
     * 
     * @param bool $incDeleted 更新スタッフに論理削除も含める場合はtrue
     */
    public function updateWhere(array $where, array $param, bool $incDeleted = true
    ) : int
    {
        $query = $incDeleted ? $this->staff->withTrashed() : $this->staff;
        foreach ($where as $k => $v) {
            $query = $query->where($k, $v);
        }
        $query->update($param);
        return true;
    }

}
