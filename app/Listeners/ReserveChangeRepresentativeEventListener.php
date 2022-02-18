<?php

namespace App\Listeners;

use App\Events\ReserveChangeRepresentativeEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Services\ReserveEstimateService;
use App\Services\WebReserveEstimateService;

class ReserveChangeRepresentativeEventListener
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
     * @param  ReserveChangeRepresentativeEvent  $event
     * @return void
     */
    public function handle(ReserveChangeRepresentativeEvent $event)
    {
        /**
         * 代表者名更新
         * ASP受付 or WEB受付でserviceを変える
         */
        if ($event->reserve->reception_type === config('consts.reserves.RECEPTION_TYPE_ASP')) {
            $this->reserveEstimateService->updateFields($event->reserve->id, [
                'representative_name' => $event->reserve->representatives->isNotEmpty() ? $event->reserve->representatives[0]->name : null
            ]);
        } elseif ($event->reserve->reception_type === config('consts.reserves.RECEPTION_TYPE_WEB')) {
            $this->webReserveEstimateService->updateFields($event->reserve->id, [
                'representative_name' => $event->reserve->representatives->isNotEmpty() ? $event->reserve->representatives[0]->name : null
            ]);
        }
    }
}
