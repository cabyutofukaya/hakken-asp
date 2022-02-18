<?php
namespace App\Repositories\WebMessage;

use App\Models\WebMessage;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * web_messagesテーブルを扱うリポジトリ
 */
class WebMessageRepository implements WebMessageRepositoryInterface
{
    /**
    * @param object $webMessage
    */
    public function __construct(WebMessage $webMessage)
    {
        $this->webMessage = $webMessage;
    }


    public function find(int $id, array $with=[], array $select=[], bool $getDeleted = false): WebMessage
    {
        $query = $this->webMessage;
        
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        return $query->findOrFail($id);
    }

    /**
     * ID一覧からリストを取得
     */
    public function getByIds(array $ids, array $with=[], array $select=[], $order = "created_at", $direction = "desc", bool $getDeleted = false) : Collection
    {
        $query = $this->webMessage;
        
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        return $query->whereIn('id', $ids)->orderBy($order, $direction)->get();
    }

    /**
     * 対象IDよりも古いメッセージがある場合はtrue
     *
     * @param int $id
     * @param int $reserveId
     * @return bool
     */
    public function isExistsOlderThenId(int $id, int $reserveId) : bool
    {
        return $this->webMessage->where('reserve_id', $reserveId)->where('id', '<', $id)->orderBy('created_at', 'desc')->count() > 0;
    }

    /**
     * 最新メッセージを取得
     */
    public function getLatestMessages(int $reserveId, int $limit, array $with=[], array $select=[], bool $getDeleted = false) : Collection
    {
        $query = $this->webMessage;
        
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        return $query->where('reserve_id', $reserveId)->latest()->take($limit)->get();
    }

    /**
     * 当該IDよりも古い最新メッセージを取得
     */
    public function getMessagesOlderThanId(int $reserveId, int $olderThanId, int $limit, array $with=[], array $select=[], bool $getDeleted = false) : Collection
    {
        $query = $this->webMessage;
        
        $query = $with ? $query->with($with) : $query;
        $query = $select ? $query->select($select) : $query;
        $query = $getDeleted ? $query->withTrashed() : $query;

        return $query->where('reserve_id', $reserveId)->where('id', '<', $olderThanId)->latest()->take($limit)->get();
    }

    public function updateForIds(array $ids, array $param)
    {
        $this->webMessage->whereIn("id", $ids)->update($param);
        return true;
    }

    /**
     * メッセージ作成
     */
    public function create(array $data) : WebMessage
    {
        return $this->webMessage->create($data);
    }

    public function updateFields(int $id, array $params) : bool
    {
        $this->webMessage->where('id', $id)->update($params);
        return true;
    }

    /**
     * 当該予約IDの(会社側の)未読件数を取得
     */
    public function getAgencyUnreadCountByReserveId(int $reserveId) : int
    {
        return $this->webMessage->where('reserve_id', $reserveId)->where('senderable_type', 'App\Models\WebUser')->whereNull('read_at')->count();
    }

    /**
     * 当該予約IDの(ユーザー側の)未読件数を取得
     */
    public function getUserUnreadCountByReserveId(int $reserveId) : int
    {
        return $this->webMessage->where('reserve_id', $reserveId)->where('senderable_type', 'App\Models\Staff')->whereNull('read_at')->count();
    }
}
