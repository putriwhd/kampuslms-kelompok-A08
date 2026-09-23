<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\User;
use App\Models\Submission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Submission>
 */
class SubmissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'assignment_id' => Assignment::factory(),
            'user_id' => User::factory()->mahasiswa(),
            'file_path' => 'submissions/'.fake()->uuid().'.pdf',
            'original_name' => fake()->word().'.pdf',
            'file_size' => fake()->numberBetween(1024, 2000000),
            'note' => fake()->optional()->sentence(),
            'submitted_at' => now(),
            'is_late' => false,
        ];
    }
}
