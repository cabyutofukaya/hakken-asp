<?php

namespace App\Services;

use App\Models\Reserve;
use App\Repositories\Agency\AgencyRepository;
use App\Repositories\ReserveDeparted\ReserveDepartedRepository;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * 催行済み管理
 * ReserveEstimateServiceを継承
 */
class DepartedService
{
    public function __construct(ReserveDepartedRepository $reserveDepartedRepository, AgencyRepository $agencyRepository)
    {
        $this->reserveDepartedRepository = $reserveDepartedRepository;
        $this->agencyRepository = $agencyRepository;
    }

    /**
     * 予約IDから予約データを取得
     */
    public function find(int $id, array $with = [], array $select=[], bool $getDeleted = false) : Reserve
    {
        return $this->reserveDepartedRepository->find($id, $with, $select, $getDeleted);
    }
    /**
     * 予約番号から催行データを1件取得
     */
    public function findByControlNumber(string $controlNumber, string $agencyAccount, array $with = [], array $select=[], bool $getDeleted = false) : ?Reserve
    {
        $agencyId = $this->agencyRepository->getIdByAccount($agencyAccount);
        return $this->reserveDepartedRepository->findByControlNumber(
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

        return $this->reserveDepartedRepository->paginateByAgencyId(
            $agencyId,
            $params,
            $limit,
            $with,
            $select
        );
    }

    /**
     * 削除
     *
     * @param int $id ID
     * @param boolean $isSoftDelete 論理削除の場合はtrue。falseは物理削除
     */
    public function delete(int $id, bool $isSoftDelete=true): bool
    {
        return $this->reserveDepartedRepository->delete($id, $isSoftDelete);
    }
}
