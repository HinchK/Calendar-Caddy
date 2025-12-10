<?php

namespace App\Observers;

use App\Models\Event;
use App\Notifications\EventUpdated;

class EventObserver
{
    /**
     * Handle the Event "updated" event.
     */
    public function updated(Event $event): void
    {
        // Check if important fields changed
        if ($event->isDirty(['scheduled_at', 'location', 'registration_deadline', 'tee_time'])) {
            // Notify registered users
            foreach ($event->registrations as $registration) {
                $registration->user->notify(new EventUpdated($event));
            }
        }
    }
}
