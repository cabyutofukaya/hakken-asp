<?php

namespace App\Services;

use App\Repositories\WebReserveExt\WebReserveExtRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\WebReserveExt;

/**
 * Web予約処理のサービスクラス。
 */
class WebReserveExtService
{
    public function __construct(
        WebReserveExtRepository $webReserveExtRepository
    ) {
        $this->webReserveExtRepository = $webReserveExtRepository;
    }

    /**
     * 1件取得
     */
    public function find(int $id, array $with = [], array $select=[], bool $getDeleted = false) : WebReserveExt
    {
        return $this->webReserveExtRepository->find($id, $with, $select, $getDeleted);
    }
    
    /**
     * 相談リクエストを辞退
     *
     * @param int $webReserveExtId
     * @return bool
     */
    public function reject(int $webReserveExtId) : bool
    {
        return $this->webReserveExtRepository->updateFields($webReserveExtId, [
            'estimate_status' => config('consts.web_reserve_exts.ESTIMATE_STATUS_RESERVE_REJECTION'),
            'rejection_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * 相談リクエストを受付
     *
     * @param int $webReserveExtId
     * @return bool
     */
    public function consent(int $webReserveExtId) : bool
    {
        $this->webReserveExtRepository->updateFields(
            $webReserveExtId,
            [
                'estimate_status' => config('consts.web_reserve_exts.ESTIMATE_STATUS_ESTIMATE'),// 見積ステータスに変更
                'consent_at' => date('Y-m-d H:i:s')
            ]
        );

        return true;
    }

    public function updateFields(int $id, array $params) : bool
    {
        return $this->webReserveExtRepository->updateFields($id, $params);
    }

    /**
     * 条件で更新
     */
    public function updateWhere(array $where, array $param) : int
    {
        return $this->webReserveExtRepository->updateWhere($where, $param);
    }

}
