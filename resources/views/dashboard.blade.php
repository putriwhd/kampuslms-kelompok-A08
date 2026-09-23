<x-layout>

    <x-slot:title>
        Dashboard
    </x-slot:title>

<<<<<<< HEAD
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

    <div class="page-header" style="margin-bottom: 28px;">
=======
    {{-- Tangkap variabel $role dari request jika tidak dikirim dari controller --}}
    @php
        $roles = ['mahasiswa', 'dosen', 'admin'];
        $role = in_array(request('as'), $roles, true) ? request('as') : 'mahasiswa';
    @endphp

    <div class="page-header" style="margin-bottom: 24px;">
>>>>>>> 36082a2b9c28c40225d5f613702da30cc2679637
        <h1 class="page-title">
        KampusLMS
        </h1>

        <p class="page-lead">
            Selamat datang di KampusLMS. Anda sedang melihat tampilan sebagai
<<<<<<< HEAD
            <strong style="color: var(--pink-dark);">
                {{ ucfirst($role) }}
            </strong>.
        </p>
    </div>

    <div
        style="
            background: var(--white);
            padding: 28px;
            border-radius: var(--radius);
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            max-width: 850px;
        "
    >

        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 20px;">

            <div
                style="
                    width: 52px;
                    height: 52px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    background: #fff0f6;
                    border-radius: 12px;
                    font-size: 25px;
                "
            >
                {{ $currentRole['icon'] }}
            </div>

            <div>
                <h2
                    style="
                        margin: 0;
                        color: var(--pink-dark);
                        font-size: 21px;
                    "
                >
                    {{ $currentRole['title'] }}
                </h2>

                <span
                    style="
                        font-size: 13px;
                        color: #777;
                    "
                >
                    Akses sebagai {{ ucfirst($role) }}
                </span>
            </div>

        </div>

        <div
            style="
                height: 1px;
                background: var(--border);
                margin-bottom: 20px;
            "
        ></div>

        <p style="margin: 0; line-height: 1.7; color: #555;">
            {{ $currentRole['description'] }}

            <a
                href="{{ route('courses.index', ['as' => $role]) }}"
                class="interactive-link"
                style="
                    color: var(--pink);
                    font-weight: 600;
                    text-decoration: none;
                "
            >
                Mata Kuliah
            </a>

            {{ $currentRole['after'] }}
=======
            <strong style="color: var(--pink-dark);">{{ ucfirst($role) }}</strong>.
>>>>>>> 36082a2b9c28c40225d5f613702da30cc2679637
        </p>
    </div>

<<<<<<< HEAD
=======
    {{-- TAMPILAN DINAMIS BERDASARKAN ROLE --}}
    @if ($role === 'mahasiswa')

        <div style="background: var(--white); padding: 24px; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
            <h3 style="margin-top: 0; color: var(--pink-dark);">🎓 Selamat datang, Mahasiswa</h3>
            <p>Gunakan menu
                <a href="{{ route('courses.index', ['as' => $role]) }}" class="interactive-link" style="color: var(--pink); font-weight: 600;">
                    "Mata Kuliah"
                </a> 
                untuk melihat daftar mata kuliah yang tersedia.
            </p>
        </div>

    @elseif ($role === 'dosen')

        <div style="background: var(--white); padding: 24px; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
            <h3 style="margin-top: 0; color: var(--pink-dark);">👨‍🏫 Selamat datang, Dosen</h3>
            <p>Lihat mata kuliah yang tersedia melalui menu
                <a href="{{ route('courses.index', ['as' => $role]) }}" class="interactive-link" style="color: var(--pink); font-weight: 600;">
                    "Mata Kuliah"
                </a>. Pada tahap ini, simulasi role belum menggunakan login atau pembatasan akses.
            </p>
        </div>

    @elseif ($role === 'admin')

        <div style="background: var(--white); padding: 24px; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
            <h3 style="margin-top: 0; color: var(--pink-dark);">🛠️ Selamat datang, Admin</h3>
            <p>Kelola mata kuliah melalui menu
                <a href="{{ route('courses.index', ['as' => $role]) }}" class="interactive-link" style="color: var(--pink); font-weight: 600;">
                    "Mata Kuliah"
                </a>. Menu Pengguna tersedia di navbar untuk simulasi CRUD pengguna.
            </p>
        </div>

    @endif

>>>>>>> 36082a2b9c28c40225d5f613702da30cc2679637
</x-layout>