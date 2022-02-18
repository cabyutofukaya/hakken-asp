<?php

namespace App\Services;

use App\Models\Reserve;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * 催行済み管理
 * ReserveEstimateServiceを継承
 */
class DepartedService extends ReserveEstimateService
{

    /**
     * 見積番号から催行済みデータを1件取得
     * 
     * @param string $controlNumber 予約番号
     */
    public function findByDepartedNumber(string $controlNumber, string $agencyAccount, array $with = [], array $select=[], bool $getDeleted = false) : ?Reserve
    {
        $agencyId = $this->agencyRepository->getIdByAccount($agencyAccount);
        return $this->reserveRepository->findByDepartedNumber(
            $controlNumber,
            $agencyId,
            $with,
            $select,
            $getDeleted
        );
    }

    /**
     * 一覧を取得
     * スコープは催行状態に設定
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
            config('consts.reserves.APPLICATION_STEP_DEPARTED'), // 催行状態
            $params,
            $limit,
            $with,
            $select
        );
    }

}
