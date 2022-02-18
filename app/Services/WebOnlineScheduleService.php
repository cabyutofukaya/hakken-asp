<?php

namespace App\Services;

use App\Models\Staff;
use App\Models\WebOnlineSchedule;
use App\Repositories\WebOnlineSchedule\WebOnlineScheduleRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class WebOnlineScheduleService
{
    public function __construct(
        WebOnlineScheduleRepository $webOnlineScheduleRepository
    ) {
        $this->webOnlineScheduleRepository = $webOnlineScheduleRepository;
    }

    /**
     * IDから一件取得
     */
    public function find(int $id, array $with=[], array $select=[], bool $getDeleted = false) : WebOnlineSchedule
    {
        return $this->webOnlineScheduleRepository->find($id, $with, $select, $getDeleted);
    }

    /**
     * 予約IDから一件取得
     */
    public function findByReserveId(int $reserveId, array $with=[], array $select=[], bool $getDeleted = false) : ?WebOnlineSchedule
    {
        return $this->webOnlineScheduleRepository->findWhere(['reserve_id' => $reserveId]);
    }

    /**
     * Web予約IDから一件取得
     */
    public function findByWebReserveExtId(int $webReserveExtId, array $with=[], array $select=[], bool $getDeleted = false) : ?WebOnlineSchedule
    {
        return $this->webOnlineScheduleRepository->findWhere(['web_reserve_ext_id' => $webReserveExtId]);
    }

    /**
     * オンライン相談承諾
     */
    public function consentRequest(int $webOnlineScheduleId, Staff $staff, int $zoomApiKeyInfoId, string $zoomStartUrl, string $zoomJoinUrl, array $zoomResponse) : WebOnlineSchedule
    {
        /**
         * 既存のリクエスト情報を削除
         * ↓
         * 新しいリクエスト情報を登録
         */
        $oldRequest = $this->webOnlineScheduleRepository->find($webOnlineScheduleId);

        // 当該予約に紐づく日程を一旦全削除
        $this->webOnlineScheduleRepository->deleteWhere(['web_reserve_ext_id' => $oldRequest->web_reserve_ext_id], true); // 論理削除

        // スケジュール作成
        return $this->webOnlineScheduleRepository->create([
            'agency_id' => $oldRequest->agency_id,
            'web_reserve_ext_id' => $oldRequest->web_reserve_ext_id,
            'reserve_id' => $oldRequest->reserve_id,
            'requesterable_type' => get_class($staff),
            'requesterable_id' => $staff->id,
            'consult_date' => $oldRequest->consult_date,
            'request_status' => config('consts.web_online_schedules.ONLINE_REQUEST_STATUS_CONSENT'),
            // zoom関連
            'zoom_api_key_id' => $zoomApiKeyInfoId,
            'zoom_start_url' => $zoomStartUrl,
            'zoom_join_url' => $zoomJoinUrl,
            'zoom_response' => $zoomResponse,
        ]);
    }

    /**
     * 日程変更依頼
     *
     * @param int $webReserveExtId Web予約ID
     * @param int $staffId スタッフID
     * @param string $consultDate 日時
     */
    public function changeRequest(int $webReserveExtId, Staff $staff, string $consultDate) : WebOnlineSchedule
    {
        /**
         * 既存のリクエスト情報を削除
         * ↓
         * 新しいリクエスト情報を登録
         */

        $oldRequest = $this->webOnlineScheduleRepository->findWhere(['web_reserve_ext_id' => $webReserveExtId]);
        
        // 当該予約に紐づく日程を一旦全削除
        $this->webOnlineScheduleRepository->deleteWhere(['web_reserve_ext_id' => $webReserveExtId], true); // 論理削除

        // スケジュール作成
        return $this->webOnlineScheduleRepository->create([
            'agency_id' => $oldRequest->agency_id,
            'web_reserve_ext_id' => $oldRequest->web_reserve_ext_id,
            'reserve_id' => $oldRequest->reserve_id,
            'requesterable_type' => get_class($staff),
            'requesterable_id' => $staff->id,
            'consult_date' => $consultDate,
            'request_status' => config('consts.web_online_schedules.ONLINE_REQUEST_STATUS_CHANGE'),
        ]);
    }
}
