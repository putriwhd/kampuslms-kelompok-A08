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
                <h2 id="submissions-heading">Pengumpulan Mahasiswa</h2>

                <table>
                    <thead>
                        <tr>
                            <th>Mahasiswa</th>
                            <th>Status</th>
                            <th>Waktu pengumpulan</th>
                            <th>Status keterlambatan</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($submissionRecap as $row)
                            <tr>
                                <td>{{ $row['student']->name }}</td>

                                <td>
                                    {{ $row['submission_status'] === 'submitted'
                                        ? 'Sudah mengumpulkan'
                                        : 'Belum mengumpulkan' }}
                                </td>

                                <td>
                                    {{ $row['submitted_at']?->format('d M Y H:i') ?? '-' }}
                                </td>

                                <td>
                                    @if ($row['submission_status'] === 'not_submitted')
                                        -
                                    @elseif ($row['is_late'])
                                        <span class="status-badge status-draft">
                                            Terlambat
                                        </span>
                                    @else
                                        <span class="status-badge status-success">
                                            Tepat waktu
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    Belum ada mahasiswa yang terdaftar di kelas ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

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
                    action="{{ url('/api/v1/assignments/' . $assignment->id . '/submissions') }}"
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
                            {{ !$ownSubmission ? 'required' : '' }}
                        >

                        @error('file')
                            <p role="alert">{{ $message }}</p>
                        @enderror

                        @if ($ownSubmission)
                            <small>
                                Jika ingin mengumpulkan ulang, pilih berkas baru.
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
