<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'scheduled_at' => $this->faker->dateTimeBetween('+1 week', '+1 month'),
            'location' => $this->faker->address,
            'max_players' => $this->faker->numberBetween(10, 100),
            'registration_deadline' => $this->faker->dateTimeBetween('now', '+1 week'),
            'tee_time' => '08:00 AM',
            'holes' => 18,
            'champion' => $this->faker->name,
            'notes' => $this->faker->sentence,
        ];
    }
}
