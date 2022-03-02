<?php

namespace App\Listeners;

use App\Services\WebMessageHistoryService;
use App\Events\ReserveUpdateStatusEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ReserveUpdateStatusEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(WebMessageHistoryService $webMessageHistoryService)
    {
        $this->webMessageHistoryService = $webMessageHistoryService;
    }

    /**
     * Handle the event.
     *
     * @param  ReserveUpdateStatusEvent  $event
     * @return void
     */
    public function handle(ReserveUpdateStatusEvent $event)
    {
        if ($event->reserve->reception_type == config('consts.reserves.RECEPTION_TYPE_WEB')) { // Web受付予約用の処理
            // メッセージ履歴レコードが存在すれば予約ステータス値を更新
            if ($this->webMessageHistoryService->isExistsByReserveId($event->reserve->id)) {
                // 見積もり状態 or 予約状態でステータス値を切り替え
                if ($event->reserve->application_step == config('consts.reserves.APPLICATION_STEP_DRAFT')) { // 予約前
                    $this->webMessageHistoryService->updateReserveStatus($event->reserve->id, optional($event->reserve->estimate_status)->val);
                } elseif ($event->reserve->application_step == config('consts.reserves.APPLICATION_STEP_RESERVE')) { // 予約
                    $this->webMessageHistoryService->updateReserveStatus($event->reserve->id, optional($event->reserve->status)->val);
                }
            }
        }
    }
}
