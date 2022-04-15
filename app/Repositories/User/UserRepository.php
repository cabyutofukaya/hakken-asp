<?php
namespace App\Repositories\User;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

// findメソッド以外は基本的には参加者データは対象にしない(registered)
class UserRepository implements UserRepositoryInterface
{
    protected $user;

    /**
    * @param object $user
    */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function find(int $id, array $with=[], array $select=[], bool $getDeleted = false): User
    {
        $query = $this->user;
        
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        return $query->findOrFail($id);
    }

    /**
     * ユーザー番号からIDを取得
     */
    public function getIdByUserNumber(string $userNumber, int $agencyId) : ?int
    {
        return $this->user->where('user_number', $userNumber)->where('agency_id', $agencyId)->value('id');
    }

    /**
     * 検索して1件取得
     */
    public function findWhere(array $where, array $with = [], array $select = [], bool $getDeleted = false) : ?User
    {
        $query = $this->user->registered();
        
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
     * 検索して全件取得
     *
     * @param int $limit 取得件数。全取得の場合はnull
     */
    public function getWhere(array $where, array $with=[], array $select=[], $limit=null) : Collection
    {
        $query = $this->user->registered(); // 検索対象を正規登録ユーザーに絞る
        
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
     * userable_idリストを条件にID一覧を取得
     */
    public function getIdInfoByUserableId(int $agencyId, string $userableType, array $userableIds) : array
    {
        return $this->user->select(['id','userable_type','userable_id'])
            ->where('agency_id', $agencyId)
            ->where('userable_type', $userableType)
            ->whereIn('userable_id', $userableIds)
            ->get()->toArray();
    }

    /**
     * 申込者検索（全ステータスを検索）
     */
    public function applicantSearch(int $agencyId, ?string $name, ?string $userNumber, array $with = [], array $select = [], ?int $limit = null, bool $getDeleted = false) : Collection
    {
        $query = $this->user->registered(); // 検索対象を正規登録ユーザーに絞る
        
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        $query = $query->where('agency_id', $agencyId);

        if (!is_empty($name)) {
            $query = $query->whereHasMorph(
                'userable',
                ['App\Models\AspUser', 'App\Models\WebUser'],
                function (\Illuminate\Database\Eloquent\Builder $q) use ($name) {
                    $q->where('name', 'like', "%$name%")
                        ->orWhere('name_kana', 'like', "%$name%")
                        ->orWhere('name_roman', 'like', "%$name%");
                }
            );
        }
        if (!is_empty($userNumber)) {
            $query = $query->where('user_number', 'like', "%$userNumber%");
        }
        return !is_null($limit) ? $query->take($limit)->get() : $query->get();
    }

    /**
     * バルクインサート
     */
    public function insert(array $rows) : bool
    {
        $this->user->insert($rows);
        return true;
    }

    /**
     * ページネーション で取得
     *
     * @var $limit
     * @return object
     */
    public function paginate(int $limit, array $with): LengthAwarePaginator
    {
        return $this->user->registered()->with($with)->sortable()->paginate($limit);
    }

    /**
     * ページネーション で取得（for 会社ID）
     *
     * @var $limit
     * @return object
     */
    public function paginateByAgencyId(int $agencyId, array $params, int $limit, array $with, array $select) : LengthAwarePaginator
    {
        $query = $this->user->registered();
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        
        foreach ($params as $key => $val) {
            if (is_empty($val)) {
                continue;
            }

            if (strpos($key, config('consts.user_custom_items.USER_CUSTOM_ITEM_PREFIX')) === 0) { // カスタム項目
                $query = $query->whereHas('v_user_custom_values', function ($q) use ($key, $val) {
                    $q->where('key', $key)->where('val', 'like', "%$val%");
                });
            } elseif (in_array($key, ['name','name_kana','name_roman'], true)) { // userableテーブルを検索
                $query = $query->whereHasMorph(
                    'userable',
                    ['App\Models\AspUser', 'App\Models\WebUser'],
                    function (\Illuminate\Database\Eloquent\Builder $q) use ($key, $val) {
                        $q->where($key, 'like', "%$val%");
                    }
                );
            } else {
                $query = $query->where($key, 'like', "%$val%");
            }
        }

        return $query->where('users.agency_id', $agencyId)->sortable()->paginate($limit); // sortableする際にagency_idがリレーション先のテーブルにも存在するのでエラー回避のために明示的にagency_idを指定する
    }

    public function create(array $data) : User
    {
        return $this->user->create($data);
    }

    public function update(int $id, array $data): User
    {
        $user = $this->find($id);
        // $user->fill($data)->save();
        $user->update($data);
        return $user;
    }

    public function updateField(int $userId, array $params) : bool
    {
        $this->user->where('id', $userId)->update($params);
        return true;

        // $user = $this->user->findOrFail($userId);
        // foreach ($params as $k => $v) {
        //     $user->{$k} = $v; // プロパティに値をセット
        // }
        // $user->save();
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
            $this->user->destroy($id);
        } else {
            $this->find($id)->forceDelete();
        }
        return true;
    }

    // public function paginateByAgencyId(int $agencyId, int $limit, array $with): LengthAwarePaginator
    // {
    //     return $this->user->with($with)->where('agency_id', $agencyId)->sortable()->paginate($limit);
    // }

    public function findByUserNumberForAgencyId($userNumber, $agencyId): ?User
    {
        return $this->user->registered()->where('agency_id', $agencyId)->where('user_number', $userNumber)->first();
    }

    /**
     * web_user_idからユーザー情報を取得
     *
     * @param int $webUserId WebユーザーID
     * @param int $agencyId 会社ID
     * @param bool $getDeleted 論理削除を含める場合はtrue
     * @return ?User
     */
    public function findByWebUserId(int $webUserId, int $agencyId, bool $getDeleted = false) : ?User
    {
        $query = $this->user;
        $query = $getDeleted ? $query->withTrashed() : $query;

        return $query
            ->where('userable_type', 'App\Models\WebUser')
            ->where('userable_id', $webUserId)
            ->where('agency_id', $agencyId)
            ->first();
    }
}
