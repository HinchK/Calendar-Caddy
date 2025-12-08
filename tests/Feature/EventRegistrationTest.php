<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Livewire\Volt\Volt;

class EventRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_for_event()
    {
        $user = User::factory()->create();
        $event = Event::factory()->create([
            'max_players' => 10,
            'scheduled_at' => now()->addDays(5),
            'registration_deadline' => now()->addDays(4),
        ]);

        $this->actingAs($user);

        // We can use Volt testing
        Volt::test('events.show', ['event' => $event])
            ->assertSee($event->title)
            ->call('register')
            ->assertDispatched('notify'); // We dispatched 'notify' in the component

        $this->assertDatabaseHas('registrations', [
            'user_id' => $user->id,
            'event_id' => $event->id,
        ]);
    }

    public function test_user_cannot_register_if_full()
    {
        $event = Event::factory()->create([
            'max_players' => 1,
            'scheduled_at' => now()->addDays(5),
        ]);

        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $event->registrations()->create(['user_id' => $user1->id]);

        $this->actingAs($user2);

        Volt::test('events.show', ['event' => $event])
            ->call('register')
            ->assertDispatched('notify', 'Event is full.');

        $this->assertDatabaseMissing('registrations', [
            'user_id' => $user2->id,
            'event_id' => $event->id,
        ]);
    }

    public function test_user_cannot_register_after_deadline()
    {
        $event = Event::factory()->create([
            'scheduled_at' => now()->addDays(5),
            'registration_deadline' => now()->subDay(), // Deadline passed
        ]);

        $user = User::factory()->create();
        $this->actingAs($user);

        Volt::test('events.show', ['event' => $event])
            ->call('register')
            ->assertDispatched('notify', 'Registration deadline has passed.');

        $this->assertDatabaseMissing('registrations', [
            'user_id' => $user->id,
            'event_id' => $event->id,
        ]);
    }
}
