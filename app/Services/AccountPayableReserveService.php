<?php

namespace App\Services;

use App\Exceptions\ExclusiveLockException;
use App\Models\AccountPayableReserve;
use App\Models\Reserve;
use App\Repositories\AccountPayableReserve\AccountPayableReserveRepository;
use App\Repositories\Agency\AgencyRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class AccountPayableReserveService
{
    public function __construct(AgencyRepository $agencyRepository, AccountPayableReserveRepository $accountPayableReserveRepository)
    {
        $this->agencyRepository = $agencyRepository;
        $this->accountPayableReserveRepository = $accountPayableReserveRepository;
    }

    /**
     * 一覧を取得
     *
     * @param string $agencyAccount
     * @param int $applicationStep 予約段階（見積/予約）
     * @param int $limit
     * @param array $with
     * @param bool $exZero 仕入額・未払額が0円のレコードを取得しない場合はtrue
     * @param
     */
    public function paginateByAgencyAccount(string $agencyAccount, array $params, int $limit, ?string $applicationStep = null, array $with = [], array $select=[], bool $exZero = true) : LengthAwarePaginator
    {
        $agencyId = $this->agencyRepository->getIdByAccount($agencyAccount);
        return $this->accountPayableReserveRepository->paginateByAgencyId($agencyId, $params, $limit, $applicationStep, $with, $select, $exZero);
    }

    /**
     * 登録or更新
     */
    public function updateOrCreate(array $where, array $params) : AccountPayableReserve
    {
        return $this->accountPayableReserveRepository->updateOrCreate($where, $params);
    }

    public function create(array $data) : AccountPayableReserve
    {
        return $this->accountPayableReserveRepository->create($data);
    }

    public function update(int $id, array $data): AccountPayableReserve
    {
        return $this->accountPayableReserveRepository->update($id, $data);
    }

    /**
     * 当該予約レコードを一件取得
     *
     * @param int $reserveId ID
     * @param array $select 取得カラム
     * @param bool $isLock 行ロックして取得する場合はtrue
     */
    public function findByReserveId(int $reserveId, array $with = [], array $select=[], bool $isLock = false) : ?AccountPayableReserve
    {
        return $this->accountPayableReserveRepository->findByReserveId($reserveId, $with, $select, $isLock);
    }

    /**
     * 当該予約IDのNet・未払金額を更新
     * 
     * @param int $reserveId 予約ID
     * @param int $reserveItineraryId (有効)行程ID。有効行程がない場合は0以下の値を渡せばok
     */
    public function refreshAmountByReserveId(int $reserveId, ?int $reserveItineraryId)
    {
        $this->accountPayableReserveRepository->refreshAmountByReserveId($reserveId, $reserveItineraryId);
    }

}
