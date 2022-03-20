<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PriceRelatedChangeEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reserveId;
    public $updatedAt;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(int $reserveId, string $updatedAt)
    {
        $this->reserveId = $reserveId;
        $this->updatedAt = $updatedAt;
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
