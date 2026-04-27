<?php
namespace Database\Factories;
use App\Models\User;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;
/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory {
    /**
     * Define the model's default state.
     * @return array<string, mixed>
     */
    public function definition(): array {
        $startDate = fake()->dateTimeBetween('now', '+1 year');
        $endDate = fake()->dateTimeBetween($startDate, '+1 year');
        return [
            'user_id' => User::factory(),
            'name' => fake()->sentence(3),
            'location' => fake()->address(),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'start_time' => fake()->time('H:i:s'),
            'end_time' => fake()->time('H:i:s'),
            'description' => fake()->paragraph(),
        ];
    }
}