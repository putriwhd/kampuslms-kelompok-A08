<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            [
                'name' => 'Administrator KampusLMS',
                'email' => 'admin@kampuslms.test',
                'nim_nip' => 'ADM001',
                'role' => 'admin',
            ],
            [
                'name' => 'Administrator Kedua',
                'email' => 'admin2@kampuslms.test',
                'nim_nip' => 'ADM002',
                'role' => 'admin',
            ],
            [
                'name' => 'Dosen Demo',
                'email' => 'dosen@kampuslms.test',
                'nim_nip' => 'NIP001',
                'role' => 'dosen',
            ],
            [
                'name' => 'Dosen Kedua',
                'email' => 'dosen2@kampuslms.test',
                'nim_nip' => 'NIP002',
                'role' => 'dosen',
            ],
            [
                'name' => 'Dosen Ketiga',
                'email' => 'dosen3@kampuslms.test',
                'nim_nip' => 'NIP003',
                'role' => 'dosen',
            ],
            [
                'name' => 'Mahasiswa Demo',
                'email' => 'mahasiswa@kampuslms.test',
                'nim_nip' => 'MHS0001',
                'role' => 'mahasiswa',
            ],
            [
                'name' => 'Mahasiswa Kedua',
                'email' => 'mahasiswa2@kampuslms.test',
                'nim_nip' => 'MHS0002',
                'role' => 'mahasiswa',
            ],
        ];

        foreach (range(3, 30) as $studentNumber) {
            $accounts[] = [
                'name' => sprintf('Mahasiswa Demo %02d', $studentNumber),
                'email' => sprintf('mahasiswa%02d@kampuslms.test', $studentNumber),
                'nim_nip' => sprintf('MHS%04d', $studentNumber),
                'role' => 'mahasiswa',
            ];
        }

        foreach ($accounts as $account) {
            $user = User::firstOrNew([
                'email' => $account['email'],
            ]);

            if (! $user->exists) {
                $nimNip = $account['nim_nip'];
                $suffix = 1;
                while (User::where('nim_nip', $nimNip)->exists()) {
                    $nimNip = $account['nim_nip'].'-'.$suffix++;
                }

                $user->name = $account['name'];
                $user->nim_nip = $nimNip;
                $user->password = Hash::make('password');
                $user->email_verified_at = now();
                $user->role = $account['role'];
                $user->save();
            }
        }
    }
}