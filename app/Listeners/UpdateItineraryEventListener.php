<?php

namespace App\Listeners;

use App\Events\UpdateItineraryEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Services\ReserveInvoiceService;

class UpdateItineraryEventListener
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
     * @param  UpdateItineraryEvent  $event
     * @return void
     */
    public function handle(UpdateItineraryEvent $event)
    {
        //　請求金額の更新
        // $reserveInvoice = $this->reserveInvoiceService->findByReserveItineraryId($event->reserveItinerary->id, [], [], false);
    }
}
