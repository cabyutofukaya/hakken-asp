<?php

namespace App\Events;

use App\Models\AgencyDeposit;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AgencyDepositedEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $agencyDeposit;
    public $checkUpdatedAt;

    /**
     * Create a new event instance.
     *
     * @param bool $checkUpdatedAt 同時編集チェックのため更新日時をチェックする場合はtrue
     * @return void
     */
    public function __construct(AgencyDeposit $agencyDeposit, bool $checkUpdatedAt = false)
    {
        $this->agencyDeposit = $agencyDeposit;
        $this->checkUpdatedAt = $checkUpdatedAt;
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
