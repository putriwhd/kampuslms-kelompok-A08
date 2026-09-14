<?php

namespace Database\Factories;

use App\Models\Submission;
use App\Models\User;
use App\Models\Grade;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Grade>
 */
class GradeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'submission_id' => Submission::factory(),
            'graded_by' => User::factory()->dosen(),
            'score' => fake()->randomFloat(2, 40, 100),
            'feedback' => fake()->sentence(),
            'graded_at' => now(),
        ];
    }
}
