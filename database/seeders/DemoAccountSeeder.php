<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
                'name' => 'Dosen Demo',
                'email' => 'dosen@kampuslms.test',
                'nim_nip' => 'NIP001',
                'role' => 'dosen',
            ],
            [
                'name' => 'Mahasiswa Demo',
                'email' => 'mahasiswa@kampuslms.test',
                'nim_nip' => 'MHS0001',
                'role' => 'mahasiswa',
            ],
        ];

        foreach ($accounts as $account) {
            $user = User::firstOrNew(['email' => $account['email']]);
            $user->name = $account['name'];
            $user->nim_nip = $account['nim_nip'];
            $user->password = Hash::make('password');
            $user->email_verified_at = now();
            $user->role = $account['role'];
            $user->save();
        }
    }
}
