<?php

namespace App\Livewire\Components;

use Illuminate\Contracts\View\View;
use Livewire\Component;

class Notifications extends Component
{
    public $notifications = [];

    public function addNotification($type, $title, $message, $duration = 5000): void
    {
        $id = uniqid();
        $this->notifications[$id] = [
            'id' => $id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'duration' => $duration,
            'timestamp' => now(),
        ];
        $this->dispatch('auto-remove-notification', id: $id, duration: $duration);
    }

    public function removeNotification($id): void
    {
        unset($this->notifications[$id]);
    }

    public function clearAll(): void
    {
        $this->notifications = [];
    }

    public function render(): View
    {
        return view('components.notifications');
    }
}
