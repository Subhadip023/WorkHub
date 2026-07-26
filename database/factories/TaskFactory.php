<?php

namespace Database\Factories;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(2),
            'due_date' => $this->faker->boolean(80) ? $this->faker->dateTimeBetween('-5 days', '+30 days')->format('Y-m-d') : null,
            'status' => $this->faker->numberBetween(1, 4),
            'priority' => $this->faker->numberBetween(1, 4),
            'type' => $this->faker->numberBetween(1, 4),
            'project_id' => null,
            'user_id' => User::factory(),
            'assigned_to' => null,
        ];
    }
}
