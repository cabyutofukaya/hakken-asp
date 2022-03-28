<?php

namespace App\Http\Controllers\Staff\Api\Web;

use App\Events\AsyncWebOnlineChangeScheduleEvent;
use App\Events\AsyncWebOnlineConsentEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\WebOnlineScheduleUpdateRequest;
use App\Http\Resources\Staff\WebOnlineSchedule\ShowResource;
use App\Services\WebOnlineScheduleService;
use App\Services\WebReserveExtService;
use App\Services\ZoomService;
use Carbon\Carbon;
use Gate;
use Illuminate\Http\Request;

class WebOnlineScheduleController extends Controller
{
    public function __construct(WebOnlineScheduleService $webOnlineScheduleService, WebReserveExtService $webReserveExtService, ZoomService $zoomService)
    {
        $this->webOnlineScheduleService = $webOnlineScheduleService;
        $this->webReserveExtService = $webReserveExtService;
        $this->zoomService = $zoomService;
    }

    /**
     * 日程承諾
     *
     * @param int $webReserveExtId Web予約ID
     */
    public function consentRequest(Request $request, string $agencyAccount, int $webOnlineScheduleId)
    {
        $webOnlineSchedule = $this->webOnlineScheduleService->find($webOnlineScheduleId);

        if (!$webOnlineSchedule) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = \Gate::authorize('consentRequest', [$webOnlineSchedule]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        try {
            $newWebOnlineSchedule = \DB::transaction(function () use ($webOnlineSchedule) {
               // apiキー情報をランダムに取得
                $apiKeyInfo = $this->zoomService->getRandomApiInfo();

                // zoom URL発行
                $startTime = date("Y-m-d\TH:i:s", strtotime($webOnlineSchedule->consult_date)); // 開始時間
;
                $zoomResponse = $this->zoomService->createMeeting($apiKeyInfo->api_key, $apiKeyInfo->api_secret, $startTime);
                $zoomStartUrl = $zoomResponse['start_url'];
                $zoomJoinUrl = $zoomResponse['join_url'];

                $staff = auth('staff')->user();
                return $this->webOnlineScheduleService->consentRequest(
                    $webOnlineSchedule->id,
                    $staff,
                    $apiKeyInfo->id,
                    $zoomStartUrl,
                    $zoomJoinUrl,
                    $zoomResponse
                );
            });

            if ($newWebOnlineSchedule) {
                event(new AsyncWebOnlineConsentEvent($newWebOnlineSchedule));

                return new ShowResource($newWebOnlineSchedule);
            }
        } catch (\Exception $e) {
            \Log::error($e);
        }
        abort(500);
    }

    /**
     * 日程変更依頼
     *
     * @param int $webReserveExtId Web予約ID
     */
    public function changeRequest(WebOnlineScheduleUpdateRequest $request, string $agencyAccount, int $webReserveExtId)
    {
        $webOnlineSchedule = $this->webOnlineScheduleService->findByWebReserveExtId($webReserveExtId);

        if (!$webOnlineSchedule) {
            abort(404, "データが見つかりません。編集する前に画面を再読み込みして最新情報を表示してください。");
        }

        // 認可チェック
        $response = \Gate::authorize('changeRequest', [$webOnlineSchedule]);
        if (!$response->allowed()) {
            abort(403, $response->message());
        }

        try {
            $input = $request->all();

            $staff = auth('staff')->user();
            $consultDate = sprintf("%s %02d:%02d", $input['consult_date'], $input['hour'], $input['minute']);
    
            $newWebOnlineSchedule = \DB::transaction(function () use ($webReserveExtId, $staff, $consultDate) {
                return $this->webOnlineScheduleService->changeRequest($webReserveExtId, $staff, $consultDate);
            });

            if ($newWebOnlineSchedule) {
                event(new AsyncWebOnlineChangeScheduleEvent($newWebOnlineSchedule));

                return new ShowResource($newWebOnlineSchedule);
            }
        } catch (\Exception $e) {
            \Log::error($e);
        }
        abort(500);
    }
}
