<?php

namespace App\Listeners;

use App\Services\ReserveReceiptService;
use App\Events\ReserveInvoiceCreatedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ReserveInvoiceCreatedEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(ReserveReceiptService $reserveReceiptService)
    {
        $this->reserveReceiptService = $reserveReceiptService;
    }

    /**
     * Handle the event.
     *
     * @param  ReserveInvoiceCreatedEvent  $event
     * @return void
     */
    public function handle(ReserveInvoiceCreatedEvent $event)
    {
        // ひとまずまだ特に処理なし
    }
}
