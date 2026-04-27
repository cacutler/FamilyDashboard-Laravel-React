<?php
namespace Database\Factories;
use App\Models\User;
use App\Models\ToDo;
use App\ToDoType;
use Illuminate\Database\Eloquent\Factories\Factory;
/**
 * @extends Factory<ToDo>
 */
class ToDoFactory extends Factory {
    /**
     * Define the model's default state.
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'created_by' => User::factory(),
            'assigned_to' => User::factory(),
            'title' => fake()->sentence(4),
            'notes' => fake()->optional()->paragraph(),
            'type' => fake()->randomElement(ToDoType::cases())
        ];
    }
    public function chore(): static {
        return $this->state(fn(array $attributes) => ['type' => ToDoType::Chore]);
    }
    public function reminder(): static {
        return $this->state(fn(array $attributes) => ['type' => ToDoType::Reminder]);
    }
}