<?php

namespace Database\Factories;

use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->sentence(3);

        return [
            'name' => $name,
            'slug' => str_replace(' ', '-', strtolower($name)),
            'description' => $this->faker->paragraph,
            'theme' => $this->faker->safeHexColor,
            'status' => 1,
            'priority' => 2,
        ];
    }
}
