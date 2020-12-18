<?php

namespace App\Events\Chat;

use App\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendMenssage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $messageGlobal;
    public $user_idGlobal;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Message $message, int $user_id)
    {
        $this->messageGlobal = $message;
        $this->user_idGlobal = $user_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        // Log::info('broadcastOn do evento '. $this->user_id);

        return new PrivateChannel('user.' . $this->user_idGlobal);
    }

    public function broadcastAs()
    {
        return 'SendMenssage';
    }

    public function broadcastWith()
    {

        return [
            'menssage' => $this->messageGlobal
        ];
    }
}
