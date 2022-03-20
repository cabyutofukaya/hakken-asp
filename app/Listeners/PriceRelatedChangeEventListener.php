<?php

namespace App\Listeners;

use App\Services\PriceRelatedChangeService;
use App\Events\PriceRelatedChangeEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class PriceRelatedChangeEventListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(PriceRelatedChangeService $priceRelatedChangeService)
    {
        $this->priceRelatedChangeService = $priceRelatedChangeService;
    }

    /**
     * Handle the event.
     *
     * @param  PriceRelatedChangeEvent  $event
     * @return void
     */
    public function handle(PriceRelatedChangeEvent $event)
    {
        // 日時更新
        $this->priceRelatedChangeService->upsert($event->reserveId, $event->updatedAt);
    }
}
