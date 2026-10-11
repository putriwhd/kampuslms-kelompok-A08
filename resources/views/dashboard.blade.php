<x-layout>
    <x-slot:title>Dashboard</x-slot:title>

    <div class="page-header dashboard-page-header">
        <h1 class="page-title">KampusLMS</h1>

        @if (! $role)
            <p class="page-lead">
                Selamat datang di KampusLMS. Silakan login untuk mengakses dashboard sesuai role Anda.
            </p>
            <p><a href="{{ route('login') }}">Masuk ke KampusLMS</a></p>
        @else
            <p class="page-lead">
                Selamat datang, {{ ucfirst($role) }} — {{ auth()->user()->name }}.
                Dashboard Anda menampilkan data sesuai peran
                <strong class="dashboard-role">{{ ucfirst($role) }}</strong>.
            </p>
            <span class="dashboard-role-info">Akses sebagai {{ ucfirst($role) }}</span>

            <section class="dashboard-card" aria-labelledby="dashboard-summary">
                <h2 id="dashboard-summary">Ringkasan {{ ucfirst($role) }}</h2>

                <dl class="dashboard-stats">
                    @foreach ($stats as $label => $value)
                        <div class="dashboard-stat">
                            <dt>{{ $label }}</dt>
                            <dd>{{ $value }}</dd>
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
                                · {{ $course->materials_count }} materi
                                · {{ $course->assignments_count }} tugas
                            </p>
                        </article>
                    @empty
                        <p>Belum ada mata kuliah yang dapat ditampilkan.</p>
                    @endforelse
                </section>
            @endif
        @endif
    </div>
</x-layout>
