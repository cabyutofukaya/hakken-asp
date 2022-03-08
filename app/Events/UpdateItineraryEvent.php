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

// 主に書類関連の金額更新
class UpdateItineraryEvent
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
        $this->reserveItinerary = $reserveItinerary->withoutRelations(); // リレーション外し
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
