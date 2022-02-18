<?php

namespace App\Listeners;

use App\Events\AsyncWebOnlineChangeScheduleEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AsyncWebOnlineChangeScheduleEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  AsyncWebOnlineChangeScheduleEvent  $event
     * @return void
     */
    public function handle(AsyncWebOnlineChangeScheduleEvent $event)
    {
        // TODO メール送信などの非同期処理を実装予定
    }
}
