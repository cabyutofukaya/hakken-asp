<?php

namespace App\Listeners;

use App\Services\WebReserveExtService;
use App\Services\WebMessageService;
use App\Events\WebMessageReadEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class WebMessageReadEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(WebReserveExtService $webReserveExtService, WebMessageService $webMessageService)
    {
        $this->webReserveExtService = $webReserveExtService;
        $this->webMessageService = $webMessageService;
    }

    /**
     * Handle the event.
     *
     * @param  WebMessageReadEvent  $event
     * @return void
     */
    public function handle(WebMessageReadEvent $event)
    {
        // 当該コメントに紐づく予約レコードの(会社側)未読数を更新
        foreach ($event->messageIds as $messageId) {
            try {
                \DB::transaction(function () use ($messageId) {
                    $webMessage = $this->webMessageService->find($messageId, [], ['id','reserve_id']);

                    // 当該メッセージの予約情報に紐づく未読コメント数を取得
                    $unreadCount = $this->webMessageService->getAgencyUnreadCountByReserveId($webMessage->reserve_id);
    
                    // 未読コメント数を更新
                    $this->webReserveExtService->updateWhere(
                        ['reserve_id' => $webMessage->reserve_id],
                        ['agency_unread_count' => $unreadCount]
                    );
                });
            } catch (\Exception $e) {
                //
            }
        }
    }
}
