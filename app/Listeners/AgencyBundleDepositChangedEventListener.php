<?php

namespace App\Listeners;

use App\Services\ReserveBundleInvoiceService;
use App\Events\AgencyBundleDepositChangedEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AgencyBundleDepositChangedEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(ReserveBundleInvoiceService $reserveBundleInvoiceService)
    {
        $this->reserveBundleInvoiceService = $reserveBundleInvoiceService;
    }

    /**
     * Handle the event.
     *
     * @param  AgencyBundleDepositChangedEvent  $event
     * @return void
     */
    public function handle(AgencyBundleDepositChangedEvent $event)
    {
        // reserve_bundle_invoicesレコードの「入金済額」「未入金額」更新
        $this->reserveBundleInvoiceService->updateFields(
            $event->reserveBundleInvoice->id,
            [
                'deposit_amount' => $event->reserveBundleInvoice->sum_deposit,
                'not_deposit_amount' => $event->reserveBundleInvoice->sum_not_deposit,
            ]
        );

    }
}
