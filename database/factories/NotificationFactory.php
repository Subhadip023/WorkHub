<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'organization_id' => Company::factory(),
            'user_id' => User::factory(),
            'type' => $this->faker->word(),
            'title' => $this->faker->sentence(),
            'message' => $this->faker->paragraph(),
            'data' => ['key' => 'value'],
            'read_at' => null,
        ];
    }
}
