<x-layout>
    <x-slot:title>Rekapitulasi Pengumpulan — {{ $assignment->title }}</x-slot:title>

    <div class="page-header">
        <h1 class="page-title">Rekapitulasi Pengumpulan</h1>
        <p class="page-lead">
            Pantau status pengumpulan tugas mahasiswa untuk mata kuliah
            <strong>{{ $assignment->course->name }}</strong>.
        </p>
    </div>

    <article class="content-card assignment-recap-card">
        <div class="assignment-recap-header">
            <div>
                <span class="assignment-recap-label">Tugas</span>
                <h2>{{ $assignment->title }}</h2>
                <p>
                    <span>Mata Kuliah:</span>
                    <strong>{{ $assignment->course->name }}</strong>
                </p>
                <p>
                    <span>Batas Pengumpulan:</span>
                    <strong>
                        {{ $assignment->due_at?->format('d M Y, H:i') ?? '—' }}
                    </strong>
                </p>
            </div>
        </div>

        @if (session('success'))
            <div class="assignment-recap-alert" role="status">
                {{ session('success') }}
            </div>
        @endif

        @php
            $totalStudents = $submissionRecap->count();
            $totalSubmitted = $submissionRecap
                ->where('submission_status', 'submitted')
                ->count();
            $totalNotSubmitted = $totalStudents - $totalSubmitted;
            $totalLate = $submissionRecap
                ->where('is_late', true)
                ->count();

            $submissionMap = $submissions->keyBy('user_id');
        @endphp

        <section class="recap-stats" aria-labelledby="stats-heading">
            <h2 id="stats-heading" class="sr-only">
                Ringkasan Statistik Pengumpulan
            </h2>

            <div class="recap-stat-item">
                <span class="recap-stat-icon">01</span>
                <p class="recap-stat-value">{{ $totalStudents }}</p>
                <p class="recap-stat-label">Total Mahasiswa</p>
            </div>

            <div class="recap-stat-item">
                <span class="recap-stat-icon">02</span>
                <p class="recap-stat-value">{{ $totalSubmitted }}</p>
                <p class="recap-stat-label">Sudah Mengumpulkan</p>
            </div>

            <div class="recap-stat-item">
                <span class="recap-stat-icon">03</span>
                <p class="recap-stat-value">{{ $totalNotSubmitted }}</p>
                <p class="recap-stat-label">Belum Mengumpulkan</p>
            </div>

            <div class="recap-stat-item">
                <span class="recap-stat-icon">04</span>
                <p class="recap-stat-value">{{ $totalLate }}</p>
                <p class="recap-stat-label">Terlambat</p>
            </div>
        </section>

        <div class="recap-progress">
            <div>
                <p>Progres Pengumpulan</p>
                <strong>
                    {{ $totalSubmitted }} dari {{ $totalStudents }} mahasiswa
                </strong>
            </div>

            <span>
                {{ $totalStudents > 0 ? round(($totalSubmitted / $totalStudents) * 100) : 0 }}%
            </span>
        </div>

        <section class="recap-table-section" aria-labelledby="recap-table-heading">
            <div class="recap-table-header">
                <div>
                    <h2 id="recap-table-heading">Daftar Mahasiswa</h2>
                    <p>Status pengumpulan dan informasi berkas tugas mahasiswa.</p>
                </div>

                <span class="recap-total-badge">
                    {{ $totalStudents }} mahasiswa
                </span>
            </div>

            <div class="recap-table-wrapper">
                <table class="recap-table">
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama Mahasiswa</th>
                            <th>NIM</th>
                            <th>Status Pengumpulan</th>
                            <th>Waktu Submit</th>
                            <th>Keterlambatan</th>
                            <th>Nama Berkas</th>
                            <th>Ukuran</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($submissionRecap as $index => $row)
                            @php
                                $submission = $submissionMap->get($row['student_id']);
                                $submitted = $row['submission_status'] === 'submitted';
                            @endphp

                            <tr>
                                <td class="recap-number">{{ $index + 1 }}</td>

                                <td class="recap-student-name">
                                    {{ $row['student_name'] }}
                                </td>

                                <td>
                                    <code class="recap-student-id">
                                        {{ $row['student_nim_nip'] ?? '—' }}
                                    </code>
                                </td>

                                <td>
                                    @if ($submitted)
                                        <span class="recap-status recap-status-success">
                                            Sudah Mengumpulkan
                                        </span>
                                    @else
                                        <span class="recap-status recap-status-danger">
                                            Belum Mengumpulkan
                                        </span>
                                    @endif
                                </td>

                                <td class="recap-date">
                                    {{ $row['submitted_at']?->format('d M Y, H:i') ?? '—' }}
                                </td>

                                <td>
                                    @if (! $submitted)
                                        <span class="recap-empty-value">—</span>
                                    @elseif ($row['is_late'])
                                        <span class="recap-status recap-status-late">
                                            Terlambat
                                        </span>
                                    @else
                                        <span class="recap-status recap-status-success">
                                            Tepat Waktu
                                        </span>
                                    @endif
                                </td>

                                <td class="recap-file-name">
                                    @if ($submission && $submission->original_name)
                                        <span title="{{ $submission->original_name }}">
                                            {{ $submission->original_name }}
                                        </span>
                                    @else
                                        <span class="recap-empty-value">—</span>
                                    @endif
                                </td>

                                <td class="recap-date">
                                    @if ($submission && $submission->file_size)
                                        @php
                                            $bytes = (int) $submission->file_size;
                                        @endphp

                                        @if ($bytes >= 1048576)
                                            {{ number_format($bytes / 1048576, 1) }} MB
                                        @else
                                            {{ number_format($bytes / 1024, 1) }} KB
                                        @endif
                                    @else
                                        <span class="recap-empty-value">—</span>
                                    @endif
                                </td>

                                <td>
                                    @if ($submission && $submission->file_path)
                                        <a
                                            href="{{ route($role . '.courses.assignments.submissions.download', [
                                                'course' => $assignment->course,
                                                'assignment' => $assignment,
                                                'submission' => $submission,
                                            ]) }}"
                                            class="recap-download-btn"
                                            title="Unduh berkas {{ $submission->original_name }}"
                                        >
                                            Unduh
                                        </a>
                                    @else
                                        <span class="recap-empty-value">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="recap-empty-row">
                                    Belum ada mahasiswa yang terdaftar di kelas ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div class="assignment-recap-footer">
            <a
                href="{{ route($role . '.courses.assignments.scoped-show', [
                    'course' => $assignment->course,
                    'assignment' => $assignment,
                ]) }}"
                class="secondary-btn"
            >
                Kembali ke Detail Tugas
            </a>

            <a
                href="{{ route($role . '.courses.assignments.index', [
                    'course' => $assignment->course,
                    'as' => $role,
                ]) }}"
                class="primary-btn"
            >
                Daftar Tugas
            </a>
        </div>
    </article>
</x-layout>