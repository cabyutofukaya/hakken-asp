<?php
namespace App\Repositories\Agency;

use App\Models\Agency;
use Illuminate\Pagination\LengthAwarePaginator;

class AgencyRepository implements AgencyRepositoryInterface
{
    /**
    * @param object $agency
    */
    public function __construct(Agency $agency)
    {
        $this->agency = $agency;
    }

    public function find(int $id) : ?Agency
    {
        return $this->agency->find($id);
    }

    /**
     * ページネーション で取得
     *
     * @var $limit
     * @return object
     */
    public function paginate(array $params, int $limit, array $with) : LengthAwarePaginator
    {
        $query = $this->agency->with($with)->sortable();

        foreach ($params as $key => $val) {
            if (!is_empty($val)) {
                if ($key === 'company_name' || $key === 'tel' || $key === 'address') { // 会社名,電話番号,住所

                    // 半角スペース or 全角スペースで区切る
                    $strs = preg_split('/[\s|\x{3000}]+/u', $val);

                    if ($key === 'company_name') { // 会社名
                        $query = $query->where(function ($q1) use ($strs) {
                            foreach ($strs as $str) {
                                if ($str === reset($strs)) { // 最初
                                    $q1->where(function ($q2) use ($str) {
                                        $q2->where('company_name', 'LIKE', "%{$str}%")->orWhere('company_kana', 'LIKE', "%{$str}%");
                                    });
                                } else { // 2つめ以降のフレーズ検索は or でつなぐ
                                    $q1->orWhere(function ($q2) use ($str) {
                                        $q2->where('company_name', 'LIKE', "%{$str}%")->orWhere('company_kana', 'LIKE', "%{$str}%");
                                    });
                                }
                            }
                        });
                    } elseif ($key === 'tel') { // 電話番号
                        $query = $query->where(function ($q) use ($strs) {
                            foreach ($strs as $str) {
                                if ($str === reset($strs)) { // 最初
                                    $q->where('tel', 'LIKE', "%{$str}%");
                                } else { // 2つめ以降のフレーズ検索は or でつなぐ
                                    $q->orWhere('tel', 'LIKE', "%{$str}%");
                                }
                            }
                        });
                    } elseif ($key === 'address') { // 住所
                        $query = $query->where(function ($q1) use ($strs) {
                            foreach ($strs as $str) {
                                if ($str === reset($strs)) { // 最初
                                    $q1->where(function ($q2) use ($str) {
                                        $q2->whereHas('prefecture', function ($q3) use ($str) {
                                            $q3->where('name', 'LIKE', "%{$str}%");
                                        })->orWhere('address1', 'LIKE', "%{$str}%")->orWhere('address2', 'LIKE', "%{$str}%");
                                    });
                                } else { // 2つめ以降のフレーズ検索は or でつなぐ
                                    $q1->orWhere(function ($q2) use ($str) {
                                        $q2->whereHas('prefecture', function ($q3) use ($str) {
                                            $q3->where('name', 'LIKE', "%{$str}%");
                                        })->orWhere('address1', 'LIKE', "%{$str}%")->orWhere('address2', 'LIKE', "%{$str}%");
                                    });
                                }
                            }
                        });
                    }
                } else {
                    $query = $query->where($key, $val);
                }
            }
        }

        return $query->sortable()->orderBy('id', 'desc')->paginate($limit);
    }

    public function create(array $data): Agency
    {
        return $this->agency->create($data);
    }

    public function update(int $id, array $data): Agency
    {
        $agency = $this->agency->find($id);
        $agency->fill($data)->save();
        return $agency;
    }

    /**
     * 項目更新
     */
    public function updateField(int $id, array $params) : bool
    {
        $this->agency->where('id', $id)->update($params);
        return true;
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
            $this->agency->destroy($id);
        } else {
            $this->agency->find($id)->forceDelete();
        }
        return true;
    }

    /**
     * 会社アカウントからIDを取得
     *
     * @param string $account 会社アカウント
     * @return int 会社ID
     */
    public function getIdByAccount(string $account) : int
    {
        return $this->agency->where('account', $account)->value('id');
    }

    /**
     * where条件から対象レコードを1件取得
     * 渡された条件はwhereで連結
     * レコードが複数ある場合は最新レコードを取得
     *
     * @param array $conditions 条件パラメータ
     */
    public function findBy(array $conditions): ?Agency
    {
        foreach ($conditions as $key => $val) {
            $query = $this->agency->where($key, $val);
        }
        return $query->latest()->first();
    }

    /**
     * 当該アカウントが存在する場合はtrue
     *
     * @param string $account アカウント
     * @param int $exclusionId 除外ID
     */
    public function isAccountExists(string $account): bool
    {
        return $this->agency->where('account', $account)->exists();
    }

    /**
     * @param int $exclusionId 除外ID
     */
    public function selectSearchCompanyName(string $name, ?int $exclusionId, int $limit): array
    {
        $query = $this->agency->select('company_name', 'id')->where(function ($q) use ($name) {
            $q->where('company_name', 'like', "%$name%")->orWhere('company_kana', 'like', "%$name%");
        });
        $query = $exclusionId ? $query->where('id', '!=', $exclusionId) : $query;

        return $query->paginate($limit)->toArray();
    }
}
