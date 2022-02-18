<?php

namespace App\Services;

use App\Models\Reserve;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * WebReserveEstimateServiceを継承
 */
class WebReserveService extends WebReserveEstimateService
{
    /**
     * 予約番号から予約データを1件取得
     */
    public function findByControlNumber(string $controlNumber, string $agencyAccount, array $with = [], array $select=[], bool $getDeleted = false) : ?Reserve
    {
        $agencyId = $this->agencyRepository->getIdByAccount($agencyAccount);
        return $this->webReserveRepository->findByControlNumber(
            $controlNumber,
            $agencyId,
            $with,
            $select,
            $getDeleted
        );
    }

    /**
     * 一覧を取得
     * スコープは予約状態に設定
     *
     * @param string $account 会社アカウント
     * @param int $limit
     * @param array $with
     */
    public function paginateByAgencyAccount(string $account, array $params, int $limit, array $with = [], array $select=[]) : LengthAwarePaginator
    {
        $agencyId = $this->agencyRepository->getIdByAccount($account);

        return $this->webReserveRepository->paginateByAgencyId(
            $agencyId,
            config('consts.reserves.APPLICATION_STEP_RESERVE'), // 予約状態
            $params,
            $limit,
            $with,
            $select
        );
    }

}
