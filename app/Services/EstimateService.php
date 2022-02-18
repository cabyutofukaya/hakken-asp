<?php

namespace App\Services;

use App\Models\Reserve;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use App\Traits\ReserveTrait;

/**
 * 見積処理のサービスクラス。
 * ReserveEstimateServiceを継承
 */
class EstimateService extends ReserveEstimateService
{
    use ReserveTrait;
    
    /**
     * 見積番号から1件取得
     */
    public function findByEstimateNumber(string $estimateNumber, string $agencyAccount, array $with = [], array $select=[], bool $getDeleted = false) : ?Reserve
    {
        $agencyId = $this->agencyRepository->getIdByAccount($agencyAccount);
        return $this->reserveRepository->findByEstimateNumber(
            $estimateNumber,
            $agencyId,
            $with,
            $select,
            $getDeleted
        );
    }

    /**
     * 一覧を取得
     * スコープは見積状態に設定
     *
     * @param string $account 会社アカウント
     * @param int $limit
     * @param array $with
     */
    public function paginateByAgencyAccount(string $account, array $params, int $limit, array $with = [], array $select=[]) : LengthAwarePaginator
    {
        $agencyId = $this->agencyRepository->getIdByAccount($account);
        
        return $this->reserveRepository->paginateByAgencyId(
            $agencyId,
            config('consts.reserves.APPLICATION_STEP_DRAFT'), // 見積状態
            $params,
            $limit,
            $with,
            $select
        );
    }

}
