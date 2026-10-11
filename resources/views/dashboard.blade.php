<x-layout>
    <x-slot:title>Dashboard</x-slot:title>

    <div class="dashboard-page-header">
        @if (! $role)
            <section class="welcome-panel">
                <span class="welcome-eyebrow">Sistem Informasi Pembelajaran</span>
                <h1>Selamat datang di KampusLMS</h1>
                <p>
                    Satu ruang untuk mengakses kegiatan pembelajaran kampus.
                    Masuk untuk melihat dashboard dan fitur sesuai peran Anda.
                </p>
                <a class="primary-btn" href="{{ route('login') }}">Masuk ke akun</a>
            </section>
        @else
            <header class="dashboard-welcome">
                <div>
                    <span class="welcome-eyebrow">Dashboard {{ ucfirst($role) }}</span>
                    <h1>Halo, {{ auth()->user()->name }}</h1>
                    <p>Berikut ringkasan aktivitas yang relevan dengan akun Anda.</p>
                </div>
                <span class="dashboard-role-info">{{ ucfirst($role) }}</span>
            </header>

            <section class="dashboard-card" aria-labelledby="dashboard-summary">
                <h2 id="dashboard-summary">Ringkasan {{ ucfirst($role) }}</h2>

                <dl class="dashboard-stats">
                    @foreach ($stats as $label => $value)
                        <div class="dashboard-stat">
                            <dt>{{ $label }}</dt>
                            <dd>{{ number_format($value) }}</dd>
                        </div>
                    @endforeach
                </dl>
            </section>

            @if ($role !== 'admin')
                <section class="dashboard-card" aria-labelledby="dashboard-courses">
                    <h2 id="dashboard-courses">
                        {{ $role === 'dosen' ? 'Mata Kuliah yang Diampu' : 'Mata Kuliah yang Diikuti' }}
                    </h2>

                    @forelse ($courses as $course)
                        <article class="dashboard-course">
                            <h3>
                                <a href="{{ route($role . '.courses.show', [
                                    'course' => $course,
                                    'as' => $role,
                                ]) }}">
                                    {{ $course->name }}
                                </a>
                            </h3>
                            <p>
                                {{ $course->code }}
                            </p>
                            <div class="dashboard-course-meta">
                                <span>{{ $course->materials_count }} materi</span>
                                <span>{{ $course->assignments_count }} tugas</span>
                            </div>
                        </article>
                    @empty
                        <p>Belum ada mata kuliah yang dapat ditampilkan.</p>
                    @endforelse
                </section>
            @endif
        @endif
    </div>
</x-layout>
