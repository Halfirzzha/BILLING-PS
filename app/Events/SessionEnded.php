<?php

namespace App\Events;

use App\Models\PlaySession;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SessionEnded implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(public PlaySession $session) {}

    /** @return array<int, PrivateChannel> */
    public function broadcastOn(): array
    {
        return [new PrivateChannel("outlet.{$this->session->outlet_id}.stations")];
    }

    public function broadcastAs(): string
    {
        return 'session.ended';
    }

    /** @return array<string, mixed> */
    public function broadcastWith(): array
    {
        return [
            'station_id' => $this->session->station_id,
            'session_id' => $this->session->id,
            'consumed_minutes' => $this->session->consumed_minutes,
        ];
    }
}
