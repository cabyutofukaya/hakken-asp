<?php

namespace App\Listeners;

use App\Events\ChangePaymentItemAmountEvent;
use App\Services\AccountPayableItemService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ChangePaymentItemAmountEventLister
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(AccountPayableItemService $accountPayableItemService)
    {
        $this->accountPayableItemService = $accountPayableItemService;
    }

    /**
     * Handle the event.
     *
     * @param  ChangePaymentItemAmountEvent  $event
     * @return void
     */
    public function handle(ChangePaymentItemAmountEvent $event)
    {
        // account_payable_itemsの当該行程ID行の支払金額・未払金額を更新
        $this->accountPayableItemService->refreshAmountByReserveItineraryId($event->reserveItineraryId);
    }
}
