<?php

namespace App\Listeners;

use App\Services\WebMessageService;
use App\Services\WebReserveExtService;
use App\Services\WebMessageHistoryService;
use App\Events\WebMessageSendEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class WebMessageSendEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(WebMessageService $webMessageService, WebReserveExtService $webReserveExtService, WebMessageHistoryService $webMessageHistoryService)
    {
        $this->webMessageService = $webMessageService;
        $this->webReserveExtService = $webReserveExtService;
        $this->webMessageHistoryService = $webMessageHistoryService;
    }

    /**
     * Handle the event.
     *
     * @param  WebMessageSendEvent  $event
     * @return void
     */
    public function handle(WebMessageSendEvent $event)
    {
        // 当該メッセージに紐づく予約IDの(ユーザー側の)未読数を取得 → 更新
        \DB::transaction(function () use ($event) {
            $userUnreadCount = $this->webMessageService->getUserUnreadCountByReserveId($event->webMessage->reserve_id);

            $this->webReserveExtService->updateWhere(
                ['reserve_id' => $event->webMessage->reserve_id],
                ['user_unread_count' => $userUnreadCount]
            );
        });

        // メッセージ履歴レコードを更新
        $this->webMessageHistoryService->addHistory($event->webMessage->agency_id, $event->webMessage->reserve_id, $event->webMessage->message);
    }
}
