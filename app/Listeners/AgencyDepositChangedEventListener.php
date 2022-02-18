<?php

namespace App\Listeners;

use App\Events\AgencyDepositChangedEvent;
use App\Services\ReserveInvoiceService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AgencyDepositChangedEventListener
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
     * @param  AgencyDepositChangedEvent  $event
     * @return void
     */
    public function handle(AgencyDepositChangedEvent $event)
    {
        // reserve_invoicesレコードの「入金済額」「未入金額」更新
        $this->reserveInvoiceService->updateFields(
            $event->reserveInvoice->id,
            [
                'deposit_amount' => $event->reserveInvoice->sum_deposit,
                'not_deposit_amount' => $event->reserveInvoice->sum_not_deposit,
            ]
        );
    }
}
