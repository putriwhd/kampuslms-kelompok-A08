<?php

namespace Database\Seeders;

<<<<<<< HEAD
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
=======
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
>>>>>>> 36082a2b9c28c40225d5f613702da30cc2679637

class DemoAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
<<<<<<< HEAD
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
=======
        //
>>>>>>> 36082a2b9c28c40225d5f613702da30cc2679637
    }
}
