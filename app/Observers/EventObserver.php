<?php

declare(strict_types=1);

namespace App\Observers;

use App\Enums\MasterClassEnum;
use App\Models\Event;
use App\Models\MasterClass;

final class EventObserver
{
    public function created(Event $event): void
    {
        MasterClass::query()
            ->create([
                'event_id' => $event->id,
                'title' => $event->title,
                'description' => $event->description,
                'status' => MasterClassEnum::UNPUBLISHED->value,
            ]);
    }
}
