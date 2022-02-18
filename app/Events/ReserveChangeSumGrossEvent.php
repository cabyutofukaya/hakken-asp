<?php

namespace App\Events;

use App\Models\ReserveItinerary;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * 旅行代金の合計金額が変更されたときに呼ばれるイベント
 * （有効な行程が変更されたとき）
 */
class ReserveChangeSumGrossEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reserveItinerary;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ReserveItinerary $reserveItinerary)
    {
        $this->reserveItinerary = $reserveItinerary;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
