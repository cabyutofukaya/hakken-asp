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

// 当該予約の支払いステータスと未払金額計算
// 支払管理。ChangePaymentDetailAmountEventが呼ばれた後に実行すること
class ChangePaymentReserveAmountEvent
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
