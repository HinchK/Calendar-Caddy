<?php

use App\Models\Event;
use Livewire\Volt\Component;

new class extends Component {
    public $events;

    public function mount()
    {
        $this->events = Event::where('scheduled_at', '>=', now()->startOfMonth())
            ->where('scheduled_at', '<=', now()->endOfMonth()->addMonth())
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'start' => $event->scheduled_at->format('Y-m-d H:i:s'),
                    'url' => route('events.show', $event),
                ];
            });
    }
}; ?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Event Calendar</h1>
    </div>

    <flux:card>
        <div class="p-4 text-center">
            <p class="text-gray-500">
                A calendar view implementation would go here using a library like FullCalendar.
                For this MVP, we are listing events for the current and next month.
            </p>
        </div>

        <div class="space-y-4 mt-4">
            @foreach ($events as $event)
                <a href="{{ $event['url'] }}" wire:navigate class="block p-4 border rounded hover:bg-zinc-50 dark:hover:bg-zinc-800 transition">
                    <div class="font-bold">{{ $event['title'] }}</div>
                    <div class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($event['start'])->format('F j, Y g:i A') }}</div>
                </a>
            @endforeach
        </div>
    </flux:card>
</div>
