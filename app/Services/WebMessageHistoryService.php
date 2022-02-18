<?php

namespace App\Services;

use App\Repositories\Agency\AgencyRepository;
use App\Repositories\WebMessageHistory\WebMessageHistoryRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\WebMessageHistory;

/**
 * Webメッセージ履歴のサービスクラス。
 */
class WebMessageHistoryService
{
    public function __construct(
        AgencyRepository $agencyRepository,
        WebMessageHistoryRepository $webMessageHistoryRepository
    ) {
        $this->agencyRepository = $agencyRepository;
        $this->webMessageHistoryRepository = $webMessageHistoryRepository;
    }

    /**
     * データが存在するか
     */
    public function isExistsByReserveId(int $reserveId)
    {
        return $this->webMessageHistoryRepository->isExistsByReserveId($reserveId);
    }

    /**
     * 予約ステータスを更新
     * 
     * @param string $reserveStatus 予約ステータス値
     */
    public function updateReserveStatus(int $reserveId, $reserveStatus) : int
    {
        return $this->webMessageHistoryRepository->updateWhere(
            ['reserve_id' => $reserveId],
            ['reserve_status' => $reserveStatus]
        );
    }

    /**
     * 相談履歴一覧を取得
     *
     * @param string $agencyAccount 会社アカウント
     * @param int $limit
     * @param array $with
     */
    public function paginateByAgencyAccount(string $agencyAccount, array $params, int $limit, array $with = [], $select = []) : LengthAwarePaginator
    {
        $agencyId = $this->agencyRepository->getIdByAccount($agencyAccount);

        return $this->webMessageHistoryRepository->paginateByAgencyId($agencyId, $params, $limit, $with, $select);
    }

    /**
     * 送信ログを作成
     */
    public function addHistory(int $agencyId, int $reserveId, ?string $message) : WebMessageHistory
    {
        $messageLog = $this->webMessageHistoryRepository->getMessage($reserveId);
        $message = $messageLog ? sprintf("%s%s%s", $messageLog, config('consts.web_message_histories.MESSAGE_LOG_SEPARATOR'), $message) : $message;

        return $this->webMessageHistoryRepository->updateOrCreate(
            [
                'agency_id' => $agencyId,
                'reserve_id' => $reserveId,
            ],
            [
                'message_log' => $message,
            ]
        );
    }
}
