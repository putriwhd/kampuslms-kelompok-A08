<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use App\Models\Material;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Material>
 */
class MaterialFactory extends Factory
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
            'uploaded_by' => User::factory()->dosen(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'type' => 'file',
            'file_path' => 'materials/'.fake()->uuid().'.pdf',
            'original_name' => fake()->word().'.pdf',
            'file_size' => fake()->numberBetween(1024, 5000000),
            'mime_type' => 'application/pdf',
            'external_url' => null,
        ];
    }
}
