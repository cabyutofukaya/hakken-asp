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

/**
 * 予約情報更新後イベント
 */
class UpdatedReserveEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $oldReserve; // 更新前予約情報
    public $newReserve; // 更新後予約情報

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Reserve $oldReserve, Reserve $newReserve)
    {
        $this->oldReserve = $oldReserve;
        $this->newReserve = $newReserve;
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
