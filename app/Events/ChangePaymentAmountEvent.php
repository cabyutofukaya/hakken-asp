<?php

namespace App\Events;

use App\Models\AccountPayableDetail;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * 請求額と出金額の差が変更されたときに呼ばれるイベント。
 * 出金登録時、出金データ削除時、仕入金額変更時
 */
class ChangePaymentAmountEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $accountPayableDetailId;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(int $accountPayableDetailId)
    {
        $this->accountPayableDetailId = $accountPayableDetailId;
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
