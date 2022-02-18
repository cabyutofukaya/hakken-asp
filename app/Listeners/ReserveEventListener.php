<?php

namespace App\Listeners;

use App\Events\ReserveEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Services\ReserveInvoiceService;

class ReserveEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(ReserveInvoiceService $reserveInvoiceService)
    {
        $this->reserveInvoiceService = $reserveInvoiceService;
    }

    /**
     * Handle the event.
     *
     * @param  ReserveEvent  $event
     * @return void
     */
    public function handle(ReserveEvent $event)
    {
        // 現状、特に処理ナシ
    }
}
