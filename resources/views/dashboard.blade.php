<x-layout>
    <x-slot:title>
        Dashboard
    </x-slot:title>

    @php
        $authenticatedUser = auth()->user();
        $role = $role ?? $authenticatedUser?->role;
        $stats = $stats ?? [];
        $courses = $courses ?? collect();
    @endphp

    @guest
        <div class="page-header dashboard-page-header">
            <h1 class="page-title">KampusLMS</h1>
            <p class="page-lead">Silakan masuk untuk membuka dashboard sesuai peran Anda.</p>
            <a href="{{ route('login') }}">Masuk</a>
        </div>
    @else
    <div class="page-header dashboard-page-header">
        <h1 class="page-title">
            Dashboard {{ ucfirst($role) }}
        </h1>

        <p class="page-lead">
            Selamat datang, {{ $authenticatedUser->name }}.
        </p>
    </div>

    <div class="dashboard-card">
        <div class="dashboard-role-header">
            <div class="dashboard-role-icon">
                {{ ['admin' => 'A', 'dosen' => 'D', 'mahasiswa' => 'M'][$role] }}
            </div>

            <div>
                <h2 class="dashboard-role-title">
                    Ringkasan {{ ucfirst($role) }}
                </h2>

                <span class="dashboard-role-info">
                    {{ $authenticatedUser->email }}
                </span>
            </div>
        </div>

        <div class="dashboard-divider"></div>

        <dl class="dashboard-stats">
            @foreach ($stats as $label => $value)
                <div>
                    <dt>{{ $label }}</dt>
                    <dd>{{ $value }}</dd>
                </div>
            @endforeach
        </dl>

        @if ($role !== 'admin')
            <h3>Mata Kuliah</h3>
            @forelse ($courses as $course)
                <p>
                    <a href="{{ route($role . '.courses.show', [$course, 'as' => $role]) }}">
                        {{ $course->code }} - {{ $course->name }}
                    </a>
                    <span>{{ $course->materials_count }} materi · {{ $course->assignments_count }} tugas</span>
                </p>
            @empty
                <p>Belum ada mata kuliah untuk ditampilkan.</p>
            @endforelse
        @endif
    </div>
    @endguest
</x-layout>