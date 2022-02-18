<?php
namespace App\Repositories\UserMemberCard;

use App\Models\UserMemberCard;
use App\Repositories\Common\ChildElementRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class UserMemberCardRepository implements UserMemberCardRepositoryInterface, ChildElementRepositoryInterface
{
    /**
    * @param object $userMemberCard
    */
    public function __construct(UserMemberCard $userMemberCard)
    {
        $this->userMemberCard = $userMemberCard;
    }

    public function find(int $id): ?UserMemberCard
    {
        return $this->userMemberCard->find($id);
    }

    /**
     * 検索して全件取得
     */
    public function getWhere(array $where, array $with=[], array $select=[]) : Collection
    {
        $query = $this->userMemberCard;
        
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;

        foreach ($where as $key => $val) {
            $query = $query->where($key, $val);
        }
        return $query->get();
    }

    public function create(array $data): UserMemberCard
    {
        return $this->userMemberCard->create($data);
    }

    public function update(int $id, array $data): UserMemberCard
    {
        $userMemberCard = $this->find($id);
        $userMemberCard->fill($data)->save();
        return $userMemberCard;
    }

    /**
     * update or insert
     */
    public function updateOrCreate(array $attributes, array $values = []) : Model
    {
        return $this->userMemberCard->updateOrCreate(
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
            $this->userMemberCard->destroy($id);
        } else {
            $this->find($id)->forceDelete();
        }
        return true;
    }

    /**
     * 当該世代管理キー以外のレコード削除
     *
     * @param string $genKey 世代管理キー
     * @param int $userId ユーザーID
     * @param boolean $isSoftDelete 論理削除の場合はtrue
     * @return boolean
     */
    public function deleteExceptionGenKey(string $genKey, int $userId, bool $isSoftDelete): bool
    {
        foreach ($this->userMemberCard->where('gen_key', '!=', $genKey)->where('user_id', $userId)->get() as $row) {
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
