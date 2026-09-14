<x-layout>

    <x-slot:title>
        Dashboard
    </x-slot:title>

    {{-- Tangkap variabel $role dari request jika tidak dikirim dari controller --}}
    @php
        $role = $role ?? request('as', 'mahasiswa');
    @endphp

    <div class="page-header" style="margin-bottom: 24px;">
        <h1 class="page-title">
            Dashboard KampusLMS
        </h1>

        <p class="page-lead">
            Selamat datang di KampusLMS. Anda saat ini melihat tampilan sebagai 
            <strong style="color: var(--pink-dark);">{{ ucfirst($role) }}</strong>.
        </p>
    </div>

    {{-- TAMPILAN DINAMIS BERDASARKAN ROLE --}}
    @if ($role === 'mahasiswa')

        <div style="background: var(--white); padding: 24px; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
            <h3 style="margin-top: 0; color: var(--pink-dark);">🎓 Area Mahasiswa</h3>
            <p>Silakan buka menu 
                <a href="{{ route('courses.index', ['as' => $role]) }}" class="interactive-link" style="color: var(--pink); font-weight: 600;">
                    "Mata Kuliah"
                </a> 
                untuk melihat dan mengambil mata kuliah semester ini.
            </p>
        </div>

    @elseif ($role === 'dosen')

        <div style="background: var(--white); padding: 24px; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
            <h3 style="margin-top: 0; color: var(--pink-dark);">👨‍🏫 Panel Dosen</h3>
            <p>Kelola bahan ajar, penugasan, dan penilaian mahasiswa melalui menu 
                <a href="{{ route('courses.index', ['as' => $role]) }}" class="interactive-link" style="color: var(--pink); font-weight: 600;">
                    "Mata Kuliah"
                </a>.
            </p>
        </div>

    @elseif ($role === 'admin')

        <div style="background: var(--white); padding: 24px; border-radius: var(--radius); border: 1px solid var(--border); box-shadow: var(--shadow);">
            <h3 style="margin-top: 0; color: var(--pink-dark);">🛠️ Panel Administrator</h3>
            <p>Anda memiliki akses penuh untuk menambah, mengubah, dan menghapus data pada menu 
                <a href="{{ route('courses.index', ['as' => $role]) }}" class="interactive-link" style="color: var(--pink); font-weight: 600;">
                    "Mata Kuliah"
                </a>.
            </p>
        </div>

    @endif

</x-layout>