<?php

namespace App\Events;

use App\Models\ReserveBundleInvoice;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AgencyBundleDepositChangedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $reserveBundleInvoice;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(ReserveBundleInvoice $reserveBundleInvoice)
    {
        $this->reserveBundleInvoice = $reserveBundleInvoice;
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
