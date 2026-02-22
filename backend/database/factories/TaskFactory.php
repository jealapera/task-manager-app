<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'statement' => $this->faker->sentence(6),
            'status' => $this->faker->randomElement(['pending', 'completed']),
            'priority' => $this->faker->numberBetween(0, 3),
            'task_date' => $this->faker->dateTimeBetween('-7 days', '+7 days')->format('Y-m-d'),
            'sort_order' => $this->faker->numberBetween(0, 50),
        ];
    }

    public function pending(): static
    {
        return $this->state(['status' => 'pending']);
    }

    public function completed(): static
    {
        return $this->state(['status' => 'completed']);
    }
}
