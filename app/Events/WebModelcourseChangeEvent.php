<?php

namespace App\Events;

use App\Models\WebModelcourse;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebModelcourseChangeEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $oldWebModelcourse; // 更新前Webモデルコース情報
    public $newWebModelcourse; // 更新後Webモデルコース情報

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(?WebModelcourse $oldWebModelcourse, ?WebModelcourse $newWebModelcourse)
    {
        $this->oldWebModelcourse = $oldWebModelcourse ? $oldWebModelcourse->withoutRelations() : null;
        $this->newWebModelcourse = $newWebModelcourse ? $newWebModelcourse->withoutRelations() : null;
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
