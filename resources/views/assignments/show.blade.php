<x-layout>
    <x-slot:title>{{ $assignment->title }}</x-slot:title>

    <article class="content-card">
        <h1>{{ $assignment->title }}</h1>

        <p>Mata kuliah: {{ $assignment->course->name }}</p>

        <p>
            Batas pengumpulan:
            {{ $assignment->due_at?->format('d M Y H:i') ?? '-' }}
        </p>

        <p>Nilai maksimal: {{ $assignment->max_score }}</p>

        <div>
            <h2>Instruksi</h2>
            <p>{{ $assignment->instructions }}</p>
        </div>

        @can('update', $assignment)
            <p>Status: {{ ucfirst($assignment->status) }}</p>

            <a href="{{ route($role . '.assignments.edit', [
                'assignment' => $assignment,
                'as' => $role,
            ]) }}">Edit Tugas</a>
        @endcan

        @can('delete', $assignment)
            <form
                action="{{ route($role . '.assignments.destroy', [
                    'assignment' => $assignment,
                    'as' => $role,
                ]) }}"
                method="POST"
                onsubmit="return confirm('Yakin ingin menghapus tugas ini?')"
            >
                @csrf
                @method('DELETE')

                <button type="submit">Hapus Tugas</button>
            </form>
        @endcan

        @if (in_array($role, ['admin', 'dosen'], true))
            <section class="submission-list" aria-labelledby="submissions-heading">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem;margin-bottom:1rem;">
                    <h2 id="submissions-heading" style="margin:0;">Pengumpulan Mahasiswa</h2>
                    <a
                        href="{{ route($role . '.courses.assignments.submissions.index', [
                            'course' => $assignment->course,
                            'assignment' => $assignment,
                        ]) }}"
                        style="display:inline-flex;align-items:center;gap:.4rem;padding:.4rem .85rem;border-radius:6px;text-decoration:none;font-size:.9rem;background:#2563eb;color:#fff;font-weight:500;"
                    >
                        <span>Lihat Halaman Rekapitulasi Lengkap &rarr;</span>
                    </a>
                </div>

                @php
                    $totalStudents     = $submissionRecap->count();
                    $totalSubmitted    = $submissionRecap->where('submission_status', 'submitted')->count();
                    $totalNotSubmitted = $totalStudents - $totalSubmitted;
                    $totalLate         = $submissionRecap->where('is_late', true)->count();
                @endphp

                {{-- Komponen Ringkasan Statistik --}}
                <div
                    class="submission-stats"
                    style="display:flex;gap:1rem;flex-wrap:wrap;margin:1rem 0;padding:.85rem;background:rgba(0,0,0,.03);border-radius:8px;"
                >
                    <div style="flex:1;min-width:120px;text-align:center;padding:.6rem;background:#fff;border-radius:6px;box-shadow:0 1px 3px rgba(0,0,0,.08);">
                        <p style="font-size:1.6rem;font-weight:700;margin:0;">{{ $totalStudents }}</p>
                        <p style="margin:0;font-size:.8rem;color:#555;">Total Mahasiswa</p>
                    </div>
                    <div style="flex:1;min-width:120px;text-align:center;padding:.6rem;background:#fff;border-radius:6px;box-shadow:0 1px 3px rgba(0,0,0,.08);">
                        <p style="font-size:1.6rem;font-weight:700;margin:0;color:#16a34a;">{{ $totalSubmitted }}</p>
                        <p style="margin:0;font-size:.8rem;color:#555;">Sudah Mengumpulkan</p>
                    </div>
                    <div style="flex:1;min-width:120px;text-align:center;padding:.6rem;background:#fff;border-radius:6px;box-shadow:0 1px 3px rgba(0,0,0,.08);">
                        <p style="font-size:1.6rem;font-weight:700;margin:0;color:#dc2626;">{{ $totalNotSubmitted }}</p>
                        <p style="margin:0;font-size:.8rem;color:#555;">Belum Mengumpulkan</p>
                    </div>
                    <div style="flex:1;min-width:120px;text-align:center;padding:.6rem;background:#fff;border-radius:6px;box-shadow:0 1px 3px rgba(0,0,0,.08);">
                        <p style="font-size:1.6rem;font-weight:700;margin:0;color:#ea580c;">{{ $totalLate }}</p>
                        <p style="margin:0;font-size:.8rem;color:#555;">Terlambat</p>
                    </div>
                </div>

                <p style="font-weight:600;margin-bottom:.75rem;">
                    Jumlah yang sudah mengumpulkan:
                    <strong>{{ $totalSubmitted }} dari {{ $totalStudents }} mahasiswa</strong>
                </p>

                <div style="overflow-x:auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>No.</th>
                                <th>Nama</th>
                                <th>NIM</th>
                                <th>Status Pengumpulan</th>
                                <th>Waktu Submit</th>
                                <th>Badge Keterlambatan</th>
                                <th>original_name</th>
                                <th>Ukuran Berkas</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($submissionRecap as $index => $row)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><strong>{{ $row['student_name'] }}</strong></td>
                                    <td><code>{{ $row['student_nim_nip'] ?? '-' }}</code></td>
                                    <td>
                                        @if ($row['submission_status'] === 'submitted')
                                            <span class="status-badge status-success">Sudah mengumpulkan</span>
                                        @else
                                            <span class="status-badge status-draft" style="background:#fee2e2;color:#991b1b;">Belum mengumpulkan</span>
                                        @endif
                                    </td>
                                    <td>{{ $row['submitted_at']?->format('d M Y H:i') ?? '-' }}</td>
                                    <td>
                                        @if ($row['submission_status'] === 'not_submitted')
                                            -
                                        @elseif ($row['is_late'])
                                            <span class="status-badge status-draft" style="background:#fef2f2;color:#dc2626;border:1px solid #fca5a5;">
                                                Terlambat
                                            </span>
                                        @else
                                            <span class="status-badge status-success" style="background:#f0fdf4;color:#16a34a;border:1px solid #86efac;">
                                                Tepat waktu
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($row['original_name'])
                                            <span title="{{ $row['original_name'] }}">{{ Str::limit($row['original_name'], 25) }}</span>
                                        @else
                                            <span style="color:#999;">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($row['formatted_file_size'])
                                            {{ $row['formatted_file_size'] }}
                                        @else
                                            <span style="color:#999;">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($row['submission'])
                                            <a
                                                href="{{ route($role . '.courses.assignments.submissions.download', [
                                                    'course'     => $assignment->course,
                                                    'assignment' => $assignment,
                                                    'submission' => $row['submission'],
                                                ]) }}"
                                                class="btn btn-sm"
                                                style="display:inline-flex;align-items:center;gap:.25rem;padding:.25rem .55rem;border-radius:4px;font-size:.8rem;background:#2563eb;color:#fff;text-decoration:none;"
                                            >
                                                Unduh
                                            </a>
                                        @else
                                            <span style="color:#aaa;font-size:.85rem;">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" style="text-align:center;padding:1.5rem;color:#666;">
                                        Belum ada mahasiswa yang terdaftar di kelas ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @forelse ($submissions as $submission)
                    <article class="submission-card">
                        <h3>{{ $submission->student->name }}</h3>

                        <p>
                            Dikumpulkan:
                            {{ $submission->submitted_at?->format('d M Y H:i') ?? '-' }}

                            @if ($submission->is_late)
                                <span class="status-badge status-draft">Terlambat</span>
                            @else
                                <span class="status-badge status-success">Tepat waktu</span>
                            @endif
                        </p>

                        <p>
                            Berkas:
                            <a href="{{ route($role . '.courses.assignments.submissions.download', [
                                'course' => $assignment->course,
                                'assignment' => $assignment,
                                'submission' => $submission,
                            ]) }}">
                                {{ $submission->original_name }}
                            </a>
                        </p>

                        @if ($submission->file_size !== null)
                            <p>
                                Ukuran:
                                @if ($submission->file_size >= 1048576)
                                    {{ number_format($submission->file_size / 1048576, 1) }} MB
                                @else
                                    {{ number_format($submission->file_size / 1024, 1) }} KB
                                @endif
                            </p>
                        @endif

                        @if ($submission->note)
                            <p>Catatan: {{ $submission->note }}</p>
                        @endif

                        @if ($submission->grade)
                            <p>
                                Nilai:
                                <strong>
                                    {{ number_format((float) $submission->grade->score, 2) }}
                                </strong>
                                / {{ $assignment->max_score }}
                            </p>

                            @if ($submission->grade->feedback)
                                <p>
                                    Umpan balik:
                                    {{ $submission->grade->feedback }}
                                </p>
                            @endif
                        @else
                            <p>Belum dinilai.</p>
                        @endif

                        @can('grade', $submission)
                            <form
                                action="{{ route('dosen.courses.assignments.submissions.grade', [
                                    'course' => $assignment->course,
                                    'assignment' => $assignment,
                                    'submission' => $submission,
                                ]) }}"
                                method="POST"
                                class="grade-form"
                            >
                                @csrf

                                <label for="score-{{ $submission->id }}">
                                    Nilai (maks. {{ $assignment->max_score }})
                                </label>

                                <input
                                    id="score-{{ $submission->id }}"
                                    name="score"
                                    type="number"
                                    min="0"
                                    max="{{ $assignment->max_score }}"
                                    step="0.01"
                                    value="{{ old('score', $submission->grade?->score) }}"
                                    required
                                >

                                <label for="feedback-{{ $submission->id }}">
                                    Umpan balik
                                </label>

                                <textarea
                                    id="feedback-{{ $submission->id }}"
                                    name="feedback"
                                    rows="3"
                                >{{ old('feedback', $submission->grade?->feedback) }}</textarea>

                                <button type="submit">Simpan Nilai</button>
                            </form>
                        @endcan
                    </article>
                @empty
                    <p>Belum ada mahasiswa yang mengumpulkan tugas ini.</p>
                @endforelse
            </section>

        @elseif ($role === 'mahasiswa')
            @php
                $deadlinePassed = $assignment->due_at
                    && now()->gt($assignment->due_at);

                $formLocked = $deadlinePassed
                    && !$assignment->allow_late;
            @endphp

            <section
                class="submission-form-card"
                aria-labelledby="submission-form-heading"
            >
                <h2 id="submission-form-heading">Pengumpulan Tugas</h2>

                <p>
                    Format berkas: PDF, DOC, DOCX.
                    Ukuran maksimal 5 MB.
                </p>

                @if ($formLocked)
                    <p class="status-badge status-draft">
                        Batas pengumpulan telah berakhir.
                        Tugas tidak menerima pengumpulan terlambat.
                    </p>
                @elseif ($deadlinePassed)
                    <p>
                        Deadline telah lewat, tetapi pengumpulan terlambat
                        masih diperbolehkan.
                    </p>
                @endif

                @if (session('success'))
                    <p role="status">{{ session('success') }}</p>
                @endif

                @if ($errors->any())
                    <div role="alert">
                        <p>Pengumpulan gagal. Periksa kembali:</p>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form
                    action="{{ route('mahasiswa.courses.assignments.submissions.store', [
                        'course'     => $assignment->course,
                        'assignment' => $assignment,
                    ]) }}"
                    method="POST"
                    enctype="multipart/form-data"
                >
                    @csrf

                    <div>
                        <label for="file">Berkas Tugas</label>

                        <input
                            id="file"
                            name="file"
                            type="file"
                            accept=".pdf,.doc,.docx"
                            {{ $formLocked ? 'disabled' : '' }}
                            required
                        >

                        @error('file')
                            <p role="alert">{{ $message }}</p>
                        @enderror

                        @if ($ownSubmission)
                            <small>
                                Pengumpulan ulang mengganti berkas sebelumnya. Pilih berkas baru untuk mengirim.
                            </small>
                        @endif
                    </div>

                    <div>
                        <label for="note">Catatan (opsional)</label>

                        <textarea
                            id="note"
                            name="note"
                            rows="3"
                            {{ $formLocked ? 'disabled' : '' }}
                        >{{ old('note', $ownSubmission?->note) }}</textarea>

                        @error('note')
                            <p role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        {{ $formLocked ? 'disabled' : '' }}
                    >
                        {{ $ownSubmission ? 'Perbarui Pengumpulan' : 'Kirim Tugas' }}
                    </button>
                </form>
            </section>

            <section
                class="submission-history"
                aria-labelledby="submission-history-heading"
            >
                <h2 id="submission-history-heading">Pengumpulan Saya</h2>

                @if ($ownSubmission)
                    <article class="submission-card">
                        <p>
                            <strong>Nama berkas:</strong>
                            {{ $ownSubmission->original_name }}
                        </p>

                        <p>
                            <strong>Ukuran berkas:</strong>

                            @php
                                $bytes = (int) $ownSubmission->file_size;
                            @endphp

                            @if ($bytes >= 1048576)
                                {{ number_format($bytes / 1048576, 1) }} MB
                            @else
                                {{ number_format($bytes / 1024, 1) }} KB
                            @endif
                        </p>

                        <p>
                            <strong>Waktu pengumpulan:</strong>
                            {{ $ownSubmission->submitted_at?->format('d M Y H:i') ?? '-' }}
                        </p>

                        <p>
                            <strong>Status:</strong>

                            @if ($ownSubmission->is_late)
                                <span class="status-badge status-draft">Terlambat</span>
                            @else
                                <span class="status-badge status-success">Tepat Waktu</span>
                            @endif
                        </p>

                        @if ($ownSubmission->note)
                            <p>
                                <strong>Catatan:</strong>
                                {{ $ownSubmission->note }}
                            </p>
                        @endif

                        <a href="{{ route($role . '.courses.assignments.submissions.download', [
                            'course' => $assignment->course,
                            'assignment' => $assignment,
                            'submission' => $ownSubmission,
                        ]) }}">
                            Unduh Berkas Saya
                        </a>
                    </article>
                @else
                    <p>Belum ada berkas yang dikumpulkan.</p>
                @endif
            </section>

            <section
                class="student-grade-card"
                aria-labelledby="student-grade-heading"
            >
                <h2 id="student-grade-heading">Nilai Saya</h2>

                @if ($ownSubmission?->grade)
                    <p>
                        Nilai:
                        <strong>
                            {{ number_format((float) $ownSubmission->grade->score, 2) }}
                        </strong>
                        / {{ $assignment->max_score }}
                    </p>

                    @if ($ownSubmission->grade->feedback)
                        <p>
                            Umpan balik dosen:
                            {{ $ownSubmission->grade->feedback }}
                        </p>
                    @endif
                @elseif ($ownSubmission)
                    <p>Tugas sudah dikumpulkan dan menunggu penilaian dosen.</p>
                @else
                    <p>Belum ada pengumpulan atau nilai untuk tugas ini.</p>
                @endif
            </section>
        @endif

        <p>
            <a href="{{ route($role . '.courses.assignments.index', [
                'course' => $assignment->course,
                'as' => $role,
            ]) }}">
                Kembali ke Daftar Tugas
            </a>
        </p>
    </article>
</x-layout>
