<x-layout>

    <x-slot:title>
        Dashboard
    </x-slot:title>

    @php
        $roles = ['mahasiswa', 'dosen', 'admin'];
        $role = in_array(request('as'), $roles, true) ? request('as') : 'mahasiswa';

        $roleData = [
            'mahasiswa' => [
                'icon' => '🎓',
                'title' => 'Selamat datang, Mahasiswa',
                'description' => 'Gunakan menu',
                'after' => 'untuk melihat daftar mata kuliah yang tersedia.',
            ],
            'dosen' => [
                'icon' => '👨‍🏫',
                'title' => 'Selamat datang, Dosen',
                'description' => 'Lihat mata kuliah yang tersedia melalui menu',
                'after' => 'Pada tahap ini, simulasi role belum menggunakan login atau pembatasan akses.',
            ],
            'admin' => [
                'icon' => '🛠️',
                'title' => 'Selamat datang, Admin',
                'description' => 'Kelola mata kuliah melalui menu',
                'after' => 'Menu Pengguna tersedia di navbar untuk simulasi CRUD pengguna.',
            ],
        ];

        $currentRole = $roleData[$role];
    @endphp

    <div class="page-header dashboard-page-header">
        <h1 class="page-title">
            KampusLMS
        </h1>

        <p class="page-lead">
            Selamat datang di KampusLMS. Anda sedang melihat tampilan sebagai
            <strong class="dashboard-role">
                {{ ucfirst($role) }}
            </strong>.
        </p>
    </div>

    <div class="dashboard-card">

        <div class="dashboard-role-header">

            <div class="dashboard-role-icon">
                {{ $currentRole['icon'] }}
            </div>

            <div>
                <h2 class="dashboard-role-title">
                    {{ $currentRole['title'] }}
                </h2>

                <span class="dashboard-role-info">
                    Akses sebagai {{ ucfirst($role) }}
                </span>
            </div>

        </div>

        <div class="dashboard-divider"></div>

        <p class="dashboard-description">
            {{ $currentRole['description'] }}

            <a
                href="{{ route($role . '.courses.index', ['as' => $role]) }}"
                class="interactive-link dashboard-course-link"
            >
                Mata Kuliah
            </a>

            {{ $currentRole['after'] }}
        </p>

    </div>

</x-layout>