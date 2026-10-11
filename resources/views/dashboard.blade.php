<x-layout>

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
