<?php
namespace App\Repositories\BusinessUser;

use App\Models\BusinessUser;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class BusinessUserRepository implements BusinessUserRepositoryInterface
{
    /**
    * @param object $businessUser
    */
    public function __construct(BusinessUser $businessUser)
    {
        $this->businessUser = $businessUser;
    }

    public function find(int $id): ?BusinessUser
    {
        return $this->businessUser->find($id);
    }

    /**
     * 顧客番号からIDを取得
     */
    public function getIdByUserNumber(string $userNumber, int $agencyId) : ?int
    {
        return $this->businessUser->where('user_number', $userNumber)->where('agency_id', $agencyId)->value('id');
    }

    /**
     * 検索して1件取得
     */
    public function findWhere(array $where, array $with=[], array $select=[]) : ?BusinessUser
    {
        $query = $this->businessUser;
        
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;

        foreach ($where as $key => $val) {
            if (is_empty($val)) {
                continue;
            }
            $query = $query->where($key, $val);
        }
        return $query->first();
    }

    /**
     * 検索して全件取得
     *
     * @param int $limit 取得件数。全取得の場合はnull
     */
    public function getWhere(array $where, array $with=[], array $select=[], $limit=null) : Collection
    {
        $query = $this->businessUser;
        
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;

        foreach ($where as $key => $val) {
            if (is_empty($val)) {
                continue;
            }
            $query = $query->where($key, 'like', "%$val%");
            // $query = $query->where($key, $val);
        }
        return !is_null($limit) ? $query->take($limit)->get() : $query->get();
    }

    /**
     * ページネーション で取得
     *
     * @var $limit
     * @return object
     */
    public function paginate(int $limit, array $with): LengthAwarePaginator
    {
        return $this->businessUser->with($with)->sortable()->paginate($limit);
    }

    /**
     * ページネーション で取得（for 会社ID）
     *
     * @var $limit
     * @return object
     */
    public function paginateByAgencyId(int $agencyId, array $params, int $limit, array $with, array $select) : LengthAwarePaginator
    {
        $query = $this->businessUser;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        
        foreach ($params as $key => $val) {
            if (is_empty($val)) {
                continue;
            }

            if (strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_PREFIX')) === 0) { // カスタム項目
                $query = $query->whereHas('v_business_user_custom_values', function ($q) use ($key, $val) {
                    $q->where('key', $key)->where('val', 'like', "%$val%");
                });
            } else {
                $query = $query->where($key, 'like', "%$val%");
            }
        }

        return $query->where('business_users.agency_id', $agencyId)->sortable()->paginate($limit); // sortableする際にagency_idがリレーション先のテーブルにも存在するのでエラー回避のために明示的にagency_idを指定する
    }

    public function create(array $data) : BusinessUser
    {
        return $this->businessUser->create($data);
    }

    public function update(int $id, array $data): BusinessUser
    {
        $businessUser = $this->find($id);
        $businessUser->update($data);
        return $businessUser;
    }

    public function updateField(int $businessUserId, array $params) : bool
    {
        $this->businessUser->where('id', $businessUserId)->update($params);
        return true;

        // $businessUser = $this->businessUser->findOrFail($businessUserId);
        // foreach ($params as $k => $v) {
        //     $businessUser->{$k} = $v; // プロパティに値をセット
        // }
        // $businessUser->save();
        // return true;
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
            $this->businessUser->destroy($id);
        } else {
            $this->find($id)->forceDelete();
        }
        return true;
    }

    public function findByUserNumberForAgencyId($userNumber, $agencyId): ?BusinessUser
    {
        return $this->businessUser->where('agency_id', $agencyId)->where('user_number', $userNumber)->first();
    }
}
