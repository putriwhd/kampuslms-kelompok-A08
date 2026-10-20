<x-layout>
    <x-slot:title>Rekapitulasi Pengumpulan — {{ $assignment->title }}</x-slot:title>

    <article class="content-card">
        <h1>Rekapitulasi Pengumpulan Tugas</h1>

        <p>
            <strong>Tugas:</strong> {{ $assignment->title }}<br>
            <strong>Mata Kuliah:</strong> {{ $assignment->course->name }}<br>
            <strong>Batas Pengumpulan:</strong>
            {{ $assignment->due_at?->format('d M Y H:i') ?? '—' }}
        </p>

        @if (session('success'))
            <p role="status" style="color:green">{{ session('success') }}</p>
        @endif

        {{-- ============================================================ --}}
        {{-- KOMPONEN RINGKASAN STATISTIK                                  --}}
        {{-- ============================================================ --}}
        @php
            $totalStudents     = $submissionRecap->count();
            $totalSubmitted    = $submissionRecap->where('submission_status', 'submitted')->count();
            $totalNotSubmitted = $totalStudents - $totalSubmitted;
            $totalLate         = $submissionRecap->where('is_late', true)->count();
        @endphp

        <section
            class="submission-stats"
            aria-labelledby="stats-heading"
            style="display:flex;gap:1rem;flex-wrap:wrap;margin:1.25rem 0;padding:1rem;background:rgba(0,0,0,.03);border-radius:10px;"
        >
            <h2 id="stats-heading" class="sr-only">Ringkasan Statistik Pengumpulan</h2>

            <div style="flex:1;min-width:140px;text-align:center;padding:.75rem;background:#fff;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.08);">
                <p style="font-size:2rem;font-weight:700;margin:0;">{{ $totalStudents }}</p>
                <p style="margin:0;font-size:.85rem;color:#555;">Total Mahasiswa</p>
            </div>

            <div style="flex:1;min-width:140px;text-align:center;padding:.75rem;background:#fff;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.08);">
                <p style="font-size:2rem;font-weight:700;margin:0;color:#16a34a;">{{ $totalSubmitted }}</p>
                <p style="margin:0;font-size:.85rem;color:#555;">Sudah Mengumpulkan</p>
            </div>

            <div style="flex:1;min-width:140px;text-align:center;padding:.75rem;background:#fff;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.08);">
                <p style="font-size:2rem;font-weight:700;margin:0;color:#dc2626;">{{ $totalNotSubmitted }}</p>
                <p style="margin:0;font-size:.85rem;color:#555;">Belum Mengumpulkan</p>
            </div>

            <div style="flex:1;min-width:140px;text-align:center;padding:.75rem;background:#fff;border-radius:8px;box-shadow:0 1px 4px rgba(0,0,0,.08);">
                <p style="font-size:2rem;font-weight:700;margin:0;color:#ea580c;">{{ $totalLate }}</p>
                <p style="margin:0;font-size:.85rem;color:#555;">Terlambat</p>
            </div>
        </section>

        <p style="font-weight:600;margin-bottom:.5rem;">
            Jumlah yang sudah mengumpulkan:
            <strong>{{ $totalSubmitted }} dari {{ $totalStudents }} mahasiswa</strong>
        </p>

        {{-- ============================================================ --}}
        {{-- TABEL REKAPITULASI LENGKAP                                    --}}
        {{-- ============================================================ --}}
        <section aria-labelledby="recap-table-heading">
            <h2 id="recap-table-heading" style="margin-top:1.5rem;">Daftar Mahasiswa &amp; Status Pengumpulan</h2>

            @php
                $submissionMap = $submissions->keyBy('user_id');
            @endphp

            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:.95rem;">
                    <thead>
                        <tr style="background:rgba(0,0,0,.05);text-align:left;">
                            <th style="padding:.6rem .75rem;white-space:nowrap;">No.</th>
                            <th style="padding:.6rem .75rem;white-space:nowrap;">Nama Mahasiswa</th>
                            <th style="padding:.6rem .75rem;white-space:nowrap;">NIM</th>
                            <th style="padding:.6rem .75rem;white-space:nowrap;">Status Pengumpulan</th>
                            <th style="padding:.6rem .75rem;white-space:nowrap;">Waktu Submit</th>
                            <th style="padding:.6rem .75rem;white-space:nowrap;">Keterlambatan</th>
                            <th style="padding:.6rem .75rem;white-space:nowrap;">Nama Berkas</th>
                            <th style="padding:.6rem .75rem;white-space:nowrap;">Ukuran</th>
                            <th style="padding:.6rem .75rem;white-space:nowrap;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($submissionRecap as $index => $row)
                            @php
                                $submission = $submissionMap->get($row['student_id']);
                                $submitted  = $row['submission_status'] === 'submitted';
                            @endphp
                            <tr style="border-bottom:1px solid rgba(0,0,0,.07);">

                                <td style="padding:.6rem .75rem;color:#888;">{{ $index + 1 }}</td>

                                <td style="padding:.6rem .75rem;font-weight:500;">
                                    {{ $row['student_name'] }}
                                </td>

                                <td style="padding:.6rem .75rem;font-family:monospace;">
                                    {{ $row['student_nim_nip'] ?? '—' }}
                                </td>

                                <td style="padding:.6rem .75rem;">
                                    @if ($submitted)
                                        <span class="status-badge status-success"
                                              style="display:inline-flex;align-items:center;gap:.3rem;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                                            Sudah Mengumpulkan
                                        </span>
                                    @else
                                        <span class="status-badge status-draft"
                                              style="display:inline-flex;align-items:center;gap:.3rem;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                            Belum Mengumpulkan
                                        </span>
                                    @endif
                                </td>

                                <td style="padding:.6rem .75rem;white-space:nowrap;">
                                    {{ $row['submitted_at']?->format('d M Y, H:i') ?? '—' }}
                                </td>

                                <td style="padding:.6rem .75rem;">
                                    @if (! $submitted)
                                        <span style="color:#aaa;">—</span>
                                    @elseif ($row['is_late'])
                                        <span class="status-badge"
                                              style="background:#fee2e2;color:#dc2626;border:1px solid #fca5a5;padding:.2rem .5rem;border-radius:4px;font-size:.8rem;">
                                            Terlambat
                                        </span>
                                    @else
                                        <span class="status-badge"
                                              style="background:#dcfce7;color:#16a34a;border:1px solid #86efac;padding:.2rem .5rem;border-radius:4px;font-size:.8rem;">
                                            Tepat Waktu
                                        </span>
                                    @endif
                                </td>

                                <td style="padding:.6rem .75rem;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                    @if ($submission && $submission->original_name)
                                        <span title="{{ $submission->original_name }}">
                                            {{ $submission->original_name }}
                                        </span>
                                    @else
                                        <span style="color:#aaa;">—</span>
                                    @endif
                                </td>

                                <td style="padding:.6rem .75rem;white-space:nowrap;">
                                    @if ($submission && $submission->file_size)
                                        @php $bytes = (int) $submission->file_size; @endphp
                                        @if ($bytes >= 1048576)
                                            {{ number_format($bytes / 1048576, 1) }} MB
                                        @else
                                            {{ number_format($bytes / 1024, 1) }} KB
                                        @endif
                                    @else
                                        <span style="color:#aaa;">—</span>
                                    @endif
                                </td>

                                <td style="padding:.6rem .75rem;white-space:nowrap;">
                                    @if ($submission && $submission->file_path)
                                        <a
                                            href="{{ route($role . '.courses.assignments.submissions.download', [
                                                'course'     => $assignment->course,
                                                'assignment' => $assignment,
                                                'submission' => $submission,
                                            ]) }}"
                                            style="display:inline-flex;align-items:center;gap:.3rem;font-size:.875rem;"
                                            title="Unduh berkas {{ $submission->original_name }}"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                            Unduh
                                        </a>
                                    @else
                                        <span style="color:#aaa;font-size:.875rem;">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" style="padding:1.5rem;text-align:center;color:#888;">
                                    Belum ada mahasiswa yang terdaftar di kelas ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <div style="margin-top:1.5rem;display:flex;gap:1.5rem;flex-wrap:wrap;">
            <a href="{{ route($role . '.courses.assignments.scoped-show', [
                'course'     => $assignment->course,
                'assignment' => $assignment,
            ]) }}">
                &#8592; Kembali ke Detail Tugas
            </a>
            <a href="{{ route($role . '.courses.assignments.index', [
                'course' => $assignment->course,
                'as'     => $role,
            ]) }}">
                Daftar Tugas Mata Kuliah
            </a>
        </div>
    </article>
</x-layout>
