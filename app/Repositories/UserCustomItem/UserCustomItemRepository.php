<?php
namespace App\Repositories\UserCustomItem;

use App\Models\UserCustomItem;
use Illuminate\Support\Collection;

class UserCustomItemRepository implements UserCustomItemRepositoryInterface
{
    /**
    * @param object $userCustomItem
    */
    public function __construct(UserCustomItem $userCustomItem)
    {
        $this->userCustomItem = $userCustomItem;
    }

    /**
     * user_custom_itemを取得
     *
     * データがない場合は 404ステータス
     *
     * @param string $id
     */
    public function find(int $id): UserCustomItem
    {
        return $this->userCustomItem->findOrFail($id);
    }

    public function create(array $data): UserCustomItem
    {
        return $this->userCustomItem->create($data);
    }

    /**
     * 条件にマッチするデータを一件取得
     */
    public function findWhere(array $where, array $select = []) : ?UserCustomItem
    {
        $query = $this->userCustomItem;
        $query = $select ? $query->select($select) : $query;
        foreach ($where as $key => $val) {
            $query = $query->where($key, $val);
        }
        return $query->first();
    }

    /**
     * 条件にマッチするデータを取得
     */
    public function getWhere(array $where, array $select = null) : Collection
    {
        $query = $this->userCustomItem;
        $query = $select ? $query->select($select) : $query;
        foreach ($where as $key => $val) {
            $query = $query->where($key, $val);
        }
        return $query->get();
    }

    public function getCategoriesForAgency(int $agencyId, $category) : Collection
    {
        return $this->userCustomItem->where('agency_id', $agencyId)->where('category', $category)->orderBy('turn', 'asc')->get();
    }

    /**
     * キー配列から当該id一覧を取得
     */
    public function getByKeys(array $keys, array $with = [], array $select = []) : Collection
    {
        $query = $this->userCustomItem;
        $query = $select ? $query->select($select) : $query;
        $query = $with ? $query->with($with) : $query;

        return $query->whereIn('key', $keys)->get();
    }

    /**
     * （旅行会社ごとの）当該カテゴリコードに属するカスタム項目を取得
     *
     * @param array $where 条件
     * @param array $notWhere 否定条件
     */
    public function getByCategoryCodeForAgencyId(string $code, int $agencyId, ?bool $flg, array $with = [], array $select = [], array $where = []) : Collection
    {
        $query = $select ? $this->userCustomItem->select($select) : $this->userCustomItem->select('user_custom_items.*');
        
        $query = $with ? $query->with($with) : $query;

        $query->join('user_custom_categories', function ($join) use ($code) {
            $join->on('user_custom_categories.id', '=', 'user_custom_items.user_custom_category_id')->where('user_custom_categories.code', '=', $code);
        })->where('agency_id', $agencyId);

        $query = !is_null($flg) ? $query->where("flg", $flg) : $query;

        if ($where) { // 検索パラメータあり
            foreach ($where as $k => $v) {
                $query = $query->where($k, $v);
            }
        }

        return $query
            ->orderBy('user_custom_items.display_position', 'asc')
            ->orderBy('user_custom_items.id', 'asc')
            ->get();
    }
    
    /**
     * 存在するキーか
     * 厳密にはプレフィックス含めて検索する必要があるが、ここでは末尾のキー部分のみで判断
     *
     * @param boolean $checkDeleted 論理削除も含めてチェックする場合はtrue
     */
    public function isExistsKey(string $keyStr, int $agencyId, bool $checkDeleted = true) : bool
    {
        $query = $this->userCustomItem;
        $query = $checkDeleted ? $query->withTrashed() : $query;
        return $query->where('agency_id', $agencyId)->where('key', 'like', "%$keyStr")->exists();
    }

    /**
     * 当該コードのkeyを取得
     *
     * @param string $code 管理コード
     * @param int $agencyId 会社ID
     * @return ?string
     */
    public function getKeyByCodeForAgency(string $code, int $agencyId) : ?string
    {
        return $this->userCustomItem->where('agency_id', $agencyId)->where('code', $code)->value('key');
    }

    /**
     * 当該カテゴリ項目の中で一番大きなturn値を返す
     */
    public function maxSeqForAgency(int $agencyId, $userCustomCategoryItemId) : int
    {
        $rows = $this->userCustomItem->select('seq')->where('agency_id', $agencyId)->where('user_custom_category_item_id', $userCustomCategoryItemId)->get();
        return $rows->isEmpty() ? 0 :$rows->max('seq');
    }

    /**
     * データ更新
     *
     * @param string $id ID
     * @param array $params 更新パラメータ
     * @return UserCustomItem
     */
    public function update(string $id, $params): UserCustomItem
    {
        $this->userCustomItem->where('id', $id)->update($params);
        return $this->find($id);
    }

    /**
     * 当該管理コードのデータを取得
     */
    public function getByCodesForAgency(array $codes, int $agencyId, array $with = [], array $select = []) : Collection
    {
        $query = $this->userCustomItem;
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;

        return $query->whereIn('code', $codes)->where('agency_id', $agencyId)->get();
    }

    // /**
    //  * 当該カスタムカテゴリ項目コードに属するカスタム項目を取得
    //  *
    //  * @param string $code カスタムカテゴリ項目コード
    //  * @return Illuminate\Support\Collection
    //  */
    // public function getByUserCustomCategoryCode(int $agencyId, $code, array $select = [], $flg=null) : Collection
    // {
    //     $query = $this->userCustomItem->where('agency_id', $agencyId)->whereHas('user_custom_category', function ($q) use ($code) {
    //         $q->where("code", $code);
    //     });

    //     $query = $select ? $query->select($select) : $query;
    //     $query = !is_null($flg) ? $query->where("flg", $flg) : $query;
    //     return $query->orderBy('seq', 'asc')->get();
    // }

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
            $this->userCustomItem->destroy($id);
        } else {
            $this->find($id)->forceDelete();
        }
        return true;
    }
}
