<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserTyping implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $documentId;
    public $position;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, $documentId, $position)
    {
        $this->user = $user;
        $this->documentId = $documentId;
        $this->position = $position;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        // Menggunakan PresenceChannel agar tahu siapa saja yang sedang aktif di dokumen tersebut
        return [
            new PresenceChannel('document.' . $this->documentId),
        ];
    }

    /**
     * Data yang dikirimkan ke WebSocket Reverb
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->user->id,
            'name' => $this->user->name,
            'position' => $this->position,
        ];
    }
}