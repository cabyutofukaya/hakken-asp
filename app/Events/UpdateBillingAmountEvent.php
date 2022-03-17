<?php

namespace App\Events;

use App\Models\Reserve;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// 請求書の金額が変わる場合のイベント
class UpdateBillingAmountEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reserve;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Reserve $reserve)
    {
        // $this->reserveItinerary = $reserveItinerary->withoutRelations(); // リレーション外し
        $this->reserve = $reserve;
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
