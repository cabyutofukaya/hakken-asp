<?php

namespace App\Listeners;

use App\Events\ReserveChangeHeadcountEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Services\ReserveEstimateService;
use App\Services\WebReserveEstimateService;


class ReserveChangeHeadcountEventListener
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
     * @param  ReserveChangeHeadcountEvent  $event
     * @return void
     */
    public function handle(ReserveChangeHeadcountEvent $event)
    {
        /**
         * 参加人数更新
         * ASP受付 or WEB受付でserviceを変える
         */
        if ($event->reserve->reception_type === config('consts.reserves.RECEPTION_TYPE_ASP')) {
            $this->reserveEstimateService->updateFields($event->reserve->id, [
                'headcount' => $event->reserve->participant_except_cancellers->count()
            ]); 
        } elseif ($event->reserve->reception_type === config('consts.reserves.RECEPTION_TYPE_WEB')) {
            $this->webReserveEstimateService->updateFields($event->reserve->id, [
                'headcount' => $event->reserve->participant_except_cancellers->count()
            ]); 
        }
    }
}
