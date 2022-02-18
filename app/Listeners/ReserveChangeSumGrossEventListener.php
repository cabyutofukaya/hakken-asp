<?php

namespace App\Listeners;

use App\Events\ReserveChangeSumGrossEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Services\ReserveEstimateService;
use App\Services\WebReserveEstimateService;

class ReserveChangeSumGrossEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(ReserveEstimateService $reserveEstimateService, WebReserveEstimateService $webReserveEstimateService)
    {
        $this->reserveEstimateService = $reserveEstimateService;
        $this->webReserveEstimateService = $webReserveEstimateService;
    }

    /**
     * Handle the event.
     *
     * @param  ReserveChangeSumGrossEvent  $event
     * @return void
     */
    public function handle(ReserveChangeSumGrossEvent $event)
    {
        /**
         * 旅行代金計更新
         * ASP受付 or WEB受付でserviceを変える
         */
        if ($event->reserveItinerary->reserve->reception_type === config('consts.reserves.RECEPTION_TYPE_ASP')) {
            $this->reserveEstimateService->updateFields($event->reserveItinerary->reserve_id, [
                'sum_gross' => $event->reserveItinerary->sum_gross
            ]);
        } elseif ($event->reserveItinerary->reserve->reception_type === config('consts.reserves.RECEPTION_TYPE_WEB')) {
            $this->webReserveEstimateService->updateFields($event->reserveItinerary->reserve_id, [
                'sum_gross' => $event->reserveItinerary->sum_gross
            ]);
        }
    }
}
