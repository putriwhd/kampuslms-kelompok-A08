<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('MK###'),
            'name' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'sks' => fake()->numberBetween(2, 4),
            'lecturer_id' => User::factory()->dosen(),
            'status' => fake()->randomElement(['draft', 'active', 'archived']),
        ];
    }
}