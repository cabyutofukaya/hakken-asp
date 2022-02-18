<?php

namespace App\Listeners;

use App\Events\AsyncWebOnlineConsentEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AsyncWebOnlineConsentEventListener
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
     * @param  AsyncWebOnlineConsentEvent  $event
     * @return void
     */
    public function handle(AsyncWebOnlineConsentEvent $event)
    {
        // TODO メール送信などの非同期処理を実装予定
    }
}
