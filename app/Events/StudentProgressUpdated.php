<?php

namespace App\Events;

use App\Models\Formation;
use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentProgressUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public User $user,
        public Formation $formation,
        public array $progress
    ) {}

    public function broadcastOn()
    {
        return new PrivateChannel('student.' . $this->user->id);
    }

    public function broadcastWith()
    {
        return [
            'formation_id' => $this->formation->id,
            'progress' => $this->progress
        ];
    }
}
