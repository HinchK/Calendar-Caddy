<?php

use App\Models\Event;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public function with(): array
    {
        return [
            'events' => Event::where('scheduled_at', '>=', now())
                ->orderBy('scheduled_at')
                ->paginate(10),
        ];
    }
}; ?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Upcoming Events</h1>
    </div>

    <div class="grid gap-6">
        @forelse ($events as $event)
            <flux:card>
                <div class="flex justify-between items-start">
                    <div>
                        <flux:heading size="lg">{{ $event->title }}</flux:heading>
                        <flux:subheading>{{ $event->scheduled_at->format('l, F j, Y \a\t g:i A') }}</flux:subheading>
                    </div>
                    <flux:badge>{{ $event->location }}</flux:badge>
                </div>

                <div class="mt-4 prose dark:prose-invert">
                    {{ Str::limit($event->description, 150) }}
                </div>

                <div class="mt-6 flex justify-end">
                    <flux:button href="{{ route('events.show', $event) }}" wire:navigate>View Details</flux:button>
                </div>
            </flux:card>
        @empty
            <div class="text-center text-gray-500">
                No upcoming events found.
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $events->links() }}
    </div>
</div>
