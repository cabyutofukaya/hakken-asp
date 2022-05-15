<?php

namespace App\Listeners;

use App\Events\ChangePaymentReserveAmountEvent;
use App\Services\AccountPayableReserveService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

// 支払管理(予約)の支払残高、ステータス更新イベント
class ChangePaymentReserveAmountEventLister
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(AccountPayableReserveService $accountPayableReserveService)
    {
        $this->accountPayableReserveService = $accountPayableReserveService;
    }

    /**
     * Handle the event.
     *
     * @param  ChangePaymentReserveAmountEvent  $event
     * @return void
     */
    public function handle(ChangePaymentReserveAmountEvent $event)
    {
        $reserveItineraryId = $event->reserve->enabled_reserve_itinerary->id; //有効な行程IDが計算対象

        // account_payable_reservesの支払金額・未払金額を更新
        $this->accountPayableReserveService->refreshAmountByReserveId($event->reserve->id, $reserveItineraryId);
    }
}
