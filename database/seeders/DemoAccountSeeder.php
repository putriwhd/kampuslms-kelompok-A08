<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Course;
use Illuminate\Database\Seeder;

class DemoAccountSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Akun Dosen Demo (Tetap)
        $dosenUtama = User::create([
            'name' => 'Dosen Demo',
            'email' => 'dosen@example.com',
            'password' => bcrypt('password'),
            'role' => 'dosen',
        ]);

        // 2. Buat Dosen Tambahan Secara Acak (Pakai Faker)
        $dosenLain = User::factory(3)->create([
            'role' => 'dosen',
        ]);

        // Gabungkan semua dosen ke dalam satu kumpulan
        $semuaDosen = $dosenLain->concat([$dosenUtama]);

        // 3. Buat Akun Admin
        User::create([
            'name' => 'Admin Demo',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // 4. Buat 5 Data Mata Kuliah (Dosennya Diacak)
        for ($i = 1; $i <= 5; $i++) {
            Course::create([
                'code' => 'SI25140' . $i,
                'name' => 'Mata Kuliah Demo ' . $i,
                'description' => 'Deskripsi untuk Mata Kuliah Demo ' . $i,
                'sks' => 3,
                'lecturer_id' => $semuaDosen->random()->id, // Pilih dosen secara acak
                'status' => 'active',
            ]);
        }

        // 5. Buat Data Mahasiswa Dummy
        User::factory(10)->create([
            'role' => 'mahasiswa',
        ]);
    }
}