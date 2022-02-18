<?php
namespace App\Repositories\BusinessUserManager;

use App\Repositories\Common\ChildElementRepositoryInterface;
use App\Models\BusinessUserManager;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

class BusinessUserManagerRepository implements BusinessUserManagerRepositoryInterface, ChildElementRepositoryInterface
{
    /**
    * @param object $businessUserManager
    */
    public function __construct(BusinessUserManager $businessUserManager)
    {
        $this->businessUserManager = $businessUserManager;
    }

    public function find(int $id, array $with=[], array $select=[], bool $getDeleted = false): BusinessUserManager
    {
        $query = $this->businessUserManager;
        
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        return $query->findOrFail($id);
    }

    /**
     * 検索して1件取得
     */
    public function findWhere(array $where, array $with=[], array $select=[], bool $getDeleted = false) : ?BusinessUserManager
    {
        $query = $this->businessUserManager;
        
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        foreach ($where as $key => $val) {
            if (is_empty($val)) {
                continue;
            }
            $query = $query->where($key, $val);
        }
        return $query->first();
    }

    /**
     * 担当者を全取得
     */
    public function allByAgencyId(string $agencyId, array $with, array $select, string $order='id', string $direction='asc') : Collection
    {
        $query = $this->businessUserManager;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        return $query->where("agency_id", $agencyId)->orderBy($order, $direction)->get();
    }

    /**
     * 申込者検索
     */
    public function applicantSearch(int $agencyId, ?string $name, ?string $userNumber, array $with = [], array $select=[], ?int $limit = null, bool $getDeleted = false) : Collection
    {
        $query = $this->businessUserManager;
        
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        $query = $query->where('agency_id', $agencyId);

        if (!is_empty($name)) {
            $query = $query->where(function ($q) use ($name) {
                $q->where('name', 'like', "%$name%")
                    ->orWhere('name_roman', 'like', "%$name%");
            });
            $query = $query->orWhereHas('business_user', function ($q) use ($name) { // リレーション先の会社も検索対象
                $q->where('name', 'like', "%$name%")
                    ->orWhere('name_kana', 'like', "%$name%")
                    ->orWhere('name_roman', 'like', "%$name%");
            });
        }
        if (!is_empty($userNumber)) {
            $query = $query->where('user_number', 'like', "%$userNumber%");
            $query = $query->orWhereHas('business_user', function ($q) use ($userNumber) { // リレーション先の会社も検索対象
                $q->where('user_number', 'like', "%$userNumber%");
            });
        }
        return !is_null($limit) ? $query->take($limit)->get() : $query->get();
    }

    /**
     * 検索して全件取得
     */
    public function getWhere(array $where, array $with=[], array $select=[], bool $getDeleted = false) : Collection
    {
        $query = $this->businessUserManager;
    
        $query = $getDeleted ? $query->withTrashed() : $query;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;

        foreach ($where as $key => $val) {
            $query = $query->where($key, $val);
        }
        return $query->get();
    }

    public function create(array $data): BusinessUserManager
    {
        return $this->businessUserManager->create($data);
    }

    public function update(int $id, array $data): BusinessUserManager
    {
        $businessUserManager = $this->find($id);
        $businessUserManager->fill($data)->save();
        return $businessUserManager;
    }

    /**
     * update or insert
     */
    public function updateOrCreate(array $attributes, array $values = []) : Model
    {
        return $this->businessUserManager->updateOrCreate(
            $attributes,
            $values
        );
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
            $this->businessUserManager->destroy($id);
        } else {
            $this->find($id)->forceDelete();
        }
        return true;
    }

    /**
     * 当該世代管理キー以外のレコード削除
     *
     * @param string $genKey 世代管理キー
     * @param int $businessUserId 顧客ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue
     * @return boolean
     */
    public function deleteExceptionGenKey(string $genKey, int $businessUserId, bool $isSoftDelete): bool
    {
        foreach ($this->businessUserManager->where('gen_key', '!=', $genKey)->where('business_user_id', $businessUserId)->get() as $row) {
            // 1行ずつ削除しないと、Modelのstatic::deleting が呼ばれないようなのでforeachで1行ずつ処理
            if ($isSoftDelete) {
                $row->delete();
            } else {
                $row->forceDelete();
            }
        }
        return true;
    }
}
