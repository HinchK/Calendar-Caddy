<?php

use App\Models\Event;
use App\Models\Comment;
use App\Models\Registration;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    public Event $event;
    public string $newComment = '';

    public function mount(Event $event)
    {
        $this->event = $event->load(['comments.user', 'registrations']);
    }

    public function register()
    {
        if ($this->event->registrations()->where('user_id', Auth::id())->exists()) {
            return;
        }

        if ($this->event->registrations()->count() >= $this->event->max_players && $this->event->max_players > 0) {
            $this->dispatch('notify', 'Event is full.');
            return;
        }

        if ($this->event->registration_deadline && now()->gt($this->event->registration_deadline)) {
            $this->dispatch('notify', 'Registration deadline has passed.');
            return;
        }

        $this->event->registrations()->create([
            'user_id' => Auth::id(),
            'status' => 'confirmed',
        ]);

        $this->event->refresh();
        $this->dispatch('notify', 'Successfully registered!');
    }

    public function cancelRegistration()
    {
        $this->event->registrations()->where('user_id', Auth::id())->delete();
        $this->event->refresh();
        $this->dispatch('notify', 'Registration cancelled.');
    }

    public function addComment()
    {
        $this->validate([
            'newComment' => 'required|string|max:1000',
        ]);

        $this->event->comments()->create([
            'user_id' => Auth::id(),
            'content' => $this->newComment,
        ]);

        $this->newComment = '';
        $this->event->refresh();
    }

    public function addToCalendar()
    {
        // Simple Google Calendar link generation
        $title = urlencode($this->event->title);
        $start = $this->event->scheduled_at->format('Ymd\THis');
        // Assuming 2 hours duration if not specified
        $end = $this->event->scheduled_at->addHours(4)->format('Ymd\THis');
        $details = urlencode($this->event->description);
        $location = urlencode($this->event->location);

        return "https://calendar.google.com/calendar/render?action=TEMPLATE&text={$title}&dates={$start}/{$end}&details={$details}&location={$location}";
    }
}; ?>

<div class="space-y-6">
    <flux:card>
        <div class="flex flex-col md:flex-row justify-between gap-6">
            <div class="space-y-4 flex-1">
                <flux:heading size="xl">{{ $event->title }}</flux:heading>

                <div class="flex gap-4 text-sm text-gray-500">
                    <div class="flex items-center gap-1">
                        <flux:icon name="calendar" class="w-4 h-4" />
                        {{ $event->scheduled_at->format('l, F j, Y \a\t g:i A') }}
                    </div>
                    <div class="flex items-center gap-1">
                        <flux:icon name="map-pin" class="w-4 h-4" />
                        {{ $event->location }}
                    </div>
                </div>

                @if($event->champion)
                    <div class="p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                        <span class="font-semibold text-yellow-800 dark:text-yellow-200">🏆 Previous Champion:</span>
                        {{ $event->champion }}
                    </div>
                @endif

                <div class="prose dark:prose-invert">
                    <h3 class="font-bold text-lg">Event Details</h3>
                    <p>{{ $event->description }}</p>

                    @if($event->notes)
                        <div class="mt-4 p-4 bg-zinc-100 dark:bg-zinc-800 rounded-lg">
                            <h4 class="font-semibold">Notes</h4>
                            <p>{{ $event->notes }}</p>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-4 mt-4 text-sm">
                        <div><span class="font-semibold">Tee Time:</span> {{ $event->tee_time ?? 'TBD' }}</div>
                        <div><span class="font-semibold">Holes:</span> {{ $event->holes }}</div>
                        <div><span class="font-semibold">Deadline:</span> {{ $event->registration_deadline ? $event->registration_deadline->format('M d, Y') : 'None' }}</div>
                        <div><span class="font-semibold">Capacity:</span> {{ $event->registrations->count() }} / {{ $event->max_players ?: 'Unlimited' }}</div>
                    </div>
                </div>
            </div>

            <div class="w-full md:w-64 space-y-4">
                <flux:button :href="$this->addToCalendar()" target="_blank" icon="calendar-days" class="w-full">
                    Add to Google Calendar
                </flux:button>

                @php
                    $isRegistered = $event->registrations->where('user_id', auth()->id())->isNotEmpty();
                    $isFull = $event->max_players > 0 && $event->registrations->count() >= $event->max_players;
                    $isPastDeadline = $event->registration_deadline && now()->gt($event->registration_deadline);
                @endphp

                @if ($isRegistered)
                    <div class="p-4 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded-lg text-center">
                        You are registered!
                    </div>
                    <flux:button wire:click="cancelRegistration" variant="danger" class="w-full" onclick="confirm('Are you sure?') || event.stopImmediatePropagation()">
                        Cancel Registration
                    </flux:button>
                @elseif ($isPastDeadline)
                     <div class="p-4 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 rounded-lg text-center">
                        Registration Closed
                    </div>
                @elseif ($isFull)
                    <div class="p-4 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300 rounded-lg text-center">
                        Event Full
                    </div>
                @else
                    <flux:button wire:click="register" variant="primary" class="w-full">
                        Sign Up Now
                    </flux:button>
                @endif
            </div>
        </div>
    </flux:card>

    <flux:card>
        <flux:heading size="lg" class="mb-4">Comments</flux:heading>

        <form wire:submit="addComment" class="mb-6 space-y-2">
            <flux:textarea wire:model="newComment" placeholder="Add a comment..." required />
            <div class="flex justify-end">
                <flux:button type="submit" variant="primary">Post Comment</flux:button>
            </div>
        </form>

        <div class="space-y-4">
            @forelse($event->comments->sortByDesc('created_at') as $comment)
                <div class="flex gap-3 p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800/50">
                    <div class="flex-shrink-0">
                         <span class="flex h-8 w-8 items-center justify-center rounded-full bg-neutral-200 text-xs font-bold text-black dark:bg-neutral-700 dark:text-white">
                            {{ $comment->user->initials() }}
                        </span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-semibold">{{ $comment->user->name }}</span>
                            <span class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                        <p class="mt-1 text-sm">{{ $comment->content }}</p>
                    </div>
                </div>
            @empty
                <div class="text-gray-500 italic">No comments yet.</div>
            @endforelse
        </div>
    </flux:card>
</div>
