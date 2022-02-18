<?php

namespace App\Services;

use App\Models\WebMessage;
use Illuminate\Support\Collection;
use App\Repositories\WebMessage\WebMessageRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class WebMessageService
{
    public function __construct(
        WebMessageRepository $webMessageRepository
    ) {
        $this->webMessageRepository = $webMessageRepository;
    }

    public function find(int $id, array $with=[], array $select=[], bool $getDeleted = false) : ?WebMessage
    {
        return $this->webMessageRepository->find($id, $with, $select, $getDeleted);
    }

    /**
     * ID一覧からリスト取得
     */
    public function getByIds(array $ids, array $with=[], array $select=[], $order = "created_at", $direction = "desc", bool $getDeleted = false) : Collection
    {
        return $this->webMessageRepository->getByIds($ids, $with, $select, $order, $direction, $getDeleted);
    }

    /**
     * 既読処理(一括)を施したのちメッセージ一覧を取得
     *
     * @param int $reserveId 予約ID
     * @param ?int $olderThanId メッセージID
     * @param int $limit 取得件数
     * @return array メッセージ一覧と既読をセットしたメッセージID一覧
     */
    public function setReadAndGetMessages(int $reserveId, ?int $olderThanId, int $limit, $orderBy = "created_at", $direction = "asc") : array
    {
        if (is_null($olderThanId)) {
            // 最新メッセージを取得
            $messages = $this->webMessageRepository->getLatestMessages($reserveId, $limit, [], ['id','senderable_type','read_at'], false);
        } else {
            // 当該IDよりも古い最新メッセージを取得
            $messages = $this->webMessageRepository->getMessagesOlderThanId($reserveId, (int)$olderThanId, $limit, [], ['id','senderable_type','read_at'], false);
        }

        // 未読IDを抽出
        $targetIds = $messages->filter(function ($value, $key) {
            return $value['senderable_type'] === "App\Models\WebUser" && is_null($value['read_at']);
        })->pluck('id')->toArray();

        // 既読をセット
        $this->webMessageRepository->updateForIds($targetIds, [
            'read_at' => date('Y-m-d H:i:s')
        ]);
        
        return [
            $this->webMessageRepository->getByIds($messages->pluck("id")->toArray(), [], [], $orderBy, $direction, false),
            $targetIds,
        ];
    }

    /**
     * 対象IDよりも古いメッセージがある場合はtrue
     *
     * @param int $id
     * @param int $reserveId
     * @return bool
     */
    public function isExistsOlderThenId(?int $id, int $reserveId) : bool
    {
        return $this->webMessageRepository->isExistsOlderThenId((int)$id, $reserveId);
    }

    /**
     * 作成
     */
    public function create(array $data) : WebMessage
    {
        return $this->webMessageRepository->create($data);
    }

    /**
     * 既読処理
     *
     * @param int $id メッセージID
     */
    public function read(int $id) : bool
    {
        return $this->webMessageRepository->updateFields($id, ['read_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * 当該予約IDの(会社側の)未読件数を取得
     */
    public function getAgencyUnreadCountByReserveId(int $reserveId) : int
    {
        return $this->webMessageRepository->getAgencyUnreadCountByReserveId($reserveId);
    }

    /**
     * 当該予約IDの(ユーザー側の)未読件数を取得
     */
    public function getUserUnreadCountByReserveId(int $reserveId) : int
    {
        return $this->webMessageRepository->getUserUnreadCountByReserveId($reserveId);
    }
}
