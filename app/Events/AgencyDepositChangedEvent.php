<?php

namespace App\Events;

use App\Models\ReserveInvoice;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * 請求書の入金額が変更されたときに呼ばれるイベント
 * 入金処理時、入金削除処理時
 */
class AgencyDepositChangedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reserveInvoice;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ReserveInvoice $reserveInvoice)
    {
        $this->reserveInvoice = $reserveInvoice;
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
