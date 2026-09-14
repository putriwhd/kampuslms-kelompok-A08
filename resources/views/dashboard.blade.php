<x-layout>

    <x-slot:title>
        Dashboard
    </x-slot:title>

    {{-- Tangkap variabel $role dari request jika tidak dikirim dari controller --}}
    @php
        $roles = ['mahasiswa', 'dosen', 'admin'];
        $role = in_array(request('as'), $roles, true) ? request('as') : 'mahasiswa';
    @endphp

    <div class="page-header" style="margin-bottom: 24px;">
        <h1 class="page-title">
            Dashboard KampusLMS
        </h1>

        <p class="page-lead">
            Selamat datang di KampusLMS. Anda sedang melihat tampilan sebagai
            <strong style="color: var(--pink-dark);">{{ ucfirst($role) }}</strong>.
        </p>
    </div>

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

</x-layout>