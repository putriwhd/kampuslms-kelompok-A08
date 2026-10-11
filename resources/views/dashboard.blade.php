<x-layout>

    <x-slot:title>
        Dashboard
    </x-slot:title>

    @php
        $user = auth()->user();
        $role = $user?->role;
        $stats = $stats ?? [];
        $courses = $courses ?? collect();

        $roleData = [
            'mahasiswa' => ['icon' => '🎓', 'title' => 'Dashboard Mahasiswa'],
            'dosen' => ['icon' => '👨‍🏫', 'title' => 'Dashboard Dosen'],
            'admin' => ['icon' => '🛠️', 'title' => 'Dashboard Admin'],
        ];
    @endphp

    <div class="page-header" style="margin-bottom: 28px;">
        <h1 class="page-title">
            KampusLMS
        </h1>

        <p class="page-lead">
            @if ($user)
                Selamat datang, {{ $user->name }}. Anda masuk sebagai
                <strong style="color: var(--pink-dark);">{{ ucfirst($role) }}</strong>.
            @else
                Selamat datang di KampusLMS.
            @endif
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
        @if (!$user)
            <h2>Selamat datang di KampusLMS</h2>
            <p>Masuk untuk mengakses fitur sesuai peran akun Anda.</p>
            <a href="{{ route('login') }}" class="interactive-link">Masuk</a>
        @else
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
                    {{ $roleData[$role]['icon'] }}
            </div>

            <div>
                <h2
                    style="
                        margin: 0;
                        color: var(--pink-dark);
                        font-size: 21px;
                    "
                >
                    {{ $roleData[$role]['title'] }}
                </h2>

                <span
                    style="
                        font-size: 13px;
                        color: #777;
                    "
                >
                    {{ $user->email }}
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

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 12px; margin-bottom: 24px;">
            @foreach ($stats as $label => $value)
                <div style="padding: 14px; background: var(--blue-soft); border: 1px solid var(--border); border-radius: 8px;">
                    <dt style="color: var(--muted);">{{ $label }}</dt>
                    <dd style="margin: 4px 0 0; font-size: 22px; font-weight: 700;">{{ $value }}</dd>
                </div>
            @endforeach
        </div>

        @if ($role === 'admin')
            <p><a href="{{ route('admin.courses.index') }}">Kelola mata kuliah</a></p>
            <p><a href="{{ route('admin.users.index') }}">Kelola pengguna</a></p>
        @else
            <h3>Mata Kuliah</h3>
            @forelse ($courses as $course)
                <p>
                    <a href="{{ route($role . '.courses.show', $course) }}">{{ $course->code }} - {{ $course->name }}</a>
                    <span>{{ $course->materials_count }} materi, {{ $course->assignments_count }} tugas</span>
                </p>
            @empty
                <p>Belum ada mata kuliah untuk ditampilkan.</p>
            @endforelse
        @endif
        @endif
    </div>

</x-layout>
