<?php

namespace Database\Factories;

use App\Enums\TaskStatus;
use App\Models\User;
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
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'status' => TaskStatus::PENDING->value,
            'completed' => false,
            'due_date' => fake()->dateTimeBetween('now', '+1 month'),
            'assignee_id' => User::factory(),
        ];
    }

    /**
     * Indicate that the task is pending.
     */
    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => TaskStatus::PENDING->value,
            'completed' => false,
        ]);
    }

    /**
     * Indicate that the task is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => TaskStatus::IN_PROGRESS->value,
            'completed' => false,
        ]);
    }

    /**
     * Indicate that the task is completed.
     */
    public function completed(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => TaskStatus::COMPLETED->value,
            'completed' => true,
        ]);
    }

    /**
     * Indicate that the task is archived.
     */
    public function archived(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => TaskStatus::ARCHIVED->value,
            'completed' => true,
        ]);
    }
}
