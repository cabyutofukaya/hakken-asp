<?php

namespace App\Listeners;

use App\Events\ChangePaymentDetailAmountForItemEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Services\AccountPayableDetailService;

class ChangePaymentDetailAmountForItemEventLister
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(AccountPayableDetailService $accountPayableDetailService)
    {
        $this->accountPayableDetailService = $accountPayableDetailService;
    }

    /**
     * Handle the event.
     *
     * @param  ChangePaymentDetailAmountForItemEvent  $event
     * @return void
     */
    public function handle(ChangePaymentDetailAmountForItemEvent $event)
    {
        // 個別商品の仕入金額更新処理
        $this->accountPayableDetailService->batchRefreshAmountForItem(
            [
                'agency_id' => $event->accountPayableItem->agency_id,
                'reserve_id' => $event->accountPayableItem->reserve_id,
                'reserve_itinerary_id' => $event->accountPayableItem->reserve_itinerary_id,
                'supplier_id' => $event->accountPayableItem->supplier_id,
                'subject' => $event->accountPayableItem->subject,
                'item_id' => $event->accountPayableItem->item_id,
            ]
        );
    }
}
