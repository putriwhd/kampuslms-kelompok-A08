<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Assignment>
 */
class AssignmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'created_by' => User::factory()->dosen(),
            'title' => fake()->sentence(4),
            'instructions' => fake()->paragraph(),
            'due_at' => now()->addDays(fake()->numberBetween(1, 14)),
            'max_score' => 100,
            'allow_late' => true,
            'status' => 'published',
        ];
    }
}
