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
        $faker = fake('id_ID');

        return [
            'code' => $faker->unique()->bothify('MK###'),

            'name' => $faker->unique()->randomElement([
                'Pemrograman Web',
                'Basis Data',
                'Sistem Informasi',
                'Rekayasa Perangkat Lunak',
                'Jaringan Komputer',
                'Kecerdasan Buatan',
                'Pemrograman Berorientasi Objek',
                'Analisis dan Perancangan Sistem',
                'Keamanan Informasi',
                'Pengembangan Aplikasi Mobile',
            ]),

            'description' => $faker->randomElement([
                'Mempelajari konsep dan penerapan pemrograman dalam pengembangan aplikasi.',
                'Mempelajari pengelolaan dan perancangan basis data untuk kebutuhan sistem informasi.',
                'Mempelajari konsep dasar sistem informasi dan penerapannya dalam organisasi.',
                'Mempelajari metode pengembangan perangkat lunak secara terstruktur dan sistematis.',
                'Mempelajari konsep jaringan komputer, komunikasi data, dan konfigurasi jaringan.',
                'Mempelajari konsep kecerdasan buatan dan penerapannya dalam berbagai bidang.',
                'Mempelajari konsep pemrograman berorientasi objek untuk membangun aplikasi.',
                'Mempelajari analisis kebutuhan dan perancangan sistem informasi.',
                'Mempelajari konsep keamanan informasi dan perlindungan data.',
                'Mempelajari pengembangan aplikasi untuk perangkat mobile.',
            ]),

            'sks' => $faker->numberBetween(2, 4),

            'lecturer_id' => User::factory()->dosen(),

            'status' => $faker->randomElement([
                'draft',
                'active',
                'archived',
            ]),
        ];
    }
}