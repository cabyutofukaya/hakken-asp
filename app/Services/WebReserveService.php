<?php

namespace App\Services;

use App\Exceptions\ExclusiveLockException;
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

    /**
     * 予約キャンセル
     *
     * @param Reserve $reserve 予約情報
     * @param bool $cancelCharge キャンセルチャージの有無
     * @return boolean
     * @throws ExclusiveLockException 同時編集を検知した場合は例外を投げる
     */
    public function cancel(Reserve $reserve, bool $cancelCharge, ?string $updatedAt) : bool
    {
        if ($updatedAt && $reserve->updated_at != $updatedAt) {
            throw new ExclusiveLockException;
        }

        if (!$reserve->cancel_at) { // cancel_atカラムの値をセットするのは初回のみ
            return $this->webReserveRepository->updateFields($reserve->id, [
                'cancel_at' => date('Y-m-d H:i:s'),
                'cancel_charge' => $cancelCharge
            ]);
        } else {
            return $this->webReserveRepository->updateFields($reserve->id, [
                'cancel_charge' => $cancelCharge
            ]);
        }
    }
}
