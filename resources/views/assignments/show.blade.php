<x-layout>
    <x-slot:title>{{ $assignment->title }}</x-slot:title>

    <div class="page-header assignment-detail-page-header">
        <div>
            <h1 class="page-title">{{ $assignment->title }}</h1>
            <p class="page-lead">
                Detail tugas untuk mata kuliah
                <strong>{{ $assignment->course->name }}</strong>.
            </p>
        </div>
    </div>

    <article class="content-card assignment-detail-card">
        <header class="assignment-detail-header">
            <div>
                <span class="assignment-detail-label">Mata Kuliah</span>
                <h2>{{ $assignment->course->name }}</h2>
            </div>

            @can('update', $assignment)
                <span class="assignment-status {{ $assignment->status === 'published' ? 'is-published' : 'is-draft' }}">
                    {{ $assignment->status === 'published' ? 'Dipublikasikan' : 'Draft' }}
                </span>
            @endcan
        </header>

        <div class="assignment-detail-info">
            <div class="assignment-info-item">
                <span>Batas Pengumpulan</span>
                <strong>{{ $assignment->due_at?->format('d M Y, H:i') ?? '—' }}</strong>
            </div>
            <div class="assignment-info-item">
                <span>Nilai Maksimal</span>
                <strong>{{ $assignment->max_score }}</strong>
            </div>
        </div>

        <section class="assignment-instructions">
            <h2>Instruksi Tugas</h2>
            <p>{{ $assignment->instructions }}</p>
        </section>

        @can('update', $assignment)
            <div class="assignment-detail-actions">
                <a
                    href="{{ route($role . '.assignments.edit', [
                        'assignment' => $assignment,
                        'as' => $role,
                    ]) }}"
                    class="primary-btn"
                >
                    Edit Tugas
                </a>

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
                        <button type="submit" class="danger-btn">Hapus Tugas</button>
                    </form>
                @endcan
            </div>
        @endcan

        @can('viewSubmissions', $assignment)
            <section class="assignment-submission-summary">
                <div>
                    <h2>Pengumpulan Mahasiswa</h2>
                    <p>
                        {{ $submissions->count() }} berkas dikumpulkan dari
                        {{ $submissionRecap->count() }} mahasiswa terdaftar.
                    </p>
                </div>
                <a
                    href="{{ route($role . '.courses.assignments.submissions.index', [
                        'course' => $assignment->course,
                        'assignment' => $assignment,
                    ]) }}"
                    class="primary-btn"
                >
                    Lihat Rekap Pengumpulan
                </a>
            </section>

            @php
                $submissionByStudent = $submissions->keyBy('user_id');
            @endphp
            <div class="assignment-detail-table-wrapper">
                <table class="assignment-detail-table">
                    <thead>
                        <tr>
                            <th>Mahasiswa</th>
                            <th>Status</th>
                            <th>Berkas</th>
                            @if ($role === 'dosen')
                                <th>Penilaian</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($submissionRecap as $row)
                            @php
                                $submission = $submissionByStudent->get($row['student_id']);
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $row['student_name'] }}</strong>
                                    @if ($row['student_nim_nip'])
                                        <small>{{ $row['student_nim_nip'] }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if ($submission)
                                        <span>Sudah mengumpulkan</span>
                                        <small>{{ $submission->submitted_at?->format('d M Y, H:i') ?? '—' }}</small>
                                        <small>{{ $submission->is_late ? 'Terlambat' : 'Tepat waktu' }}</small>
                                    @else
                                        Belum mengumpulkan
                                    @endif
                                </td>
                                <td>
                                    @if ($submission && $submission->file_path)
                                        <a href="{{ route($role . '.courses.assignments.submissions.download', [
                                            'course' => $assignment->course,
                                            'assignment' => $assignment,
                                            'submission' => $submission,
                                        ]) }}">
                                            {{ $submission->original_name }}
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                                @if ($role === 'dosen')
                                    <td>
                                        @if ($submission)
                                            <form
                                                action="{{ route('dosen.courses.assignments.submissions.grade', [
                                                    'course' => $assignment->course,
                                                    'assignment' => $assignment,
                                                    'submission' => $submission,
                                                ]) }}"
                                                method="POST"
                                                class="assignment-grade-form"
                                            >
                                                @csrf
                                                <label>
                                                    <span class="sr-only">Nilai {{ $row['student_name'] }}</span>
                                                    <input
                                                        type="number"
                                                        name="score"
                                                        min="0"
                                                        max="{{ $assignment->max_score }}"
                                                        step="0.01"
                                                        value="{{ old('score', $submission->grade?->score) }}"
                                                        placeholder="Nilai / {{ $assignment->max_score }}"
                                                        required
                                                    >
                                                </label>
                                                <label>
                                                    <span class="sr-only">Umpan balik {{ $row['student_name'] }}</span>
                                                    <input
                                                        type="text"
                                                        name="feedback"
                                                        maxlength="5000"
                                                        value="{{ old('feedback', $submission->grade?->feedback) }}"
                                                        placeholder="Umpan balik (opsional)"
                                                    >
                                                </label>
                                                <button type="submit" class="primary-btn">
                                                    Simpan Nilai
                                                </button>
                                            </form>
                                        @else
                                            —
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $role === 'dosen' ? 4 : 3 }}">
                                    Belum ada mahasiswa yang terdaftar di kelas ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endcan

        @if ($role === 'mahasiswa')
            @if ($ownSubmission)
                <section class="assignment-own-submission">
                    <h2>Pengumpulan Saya</h2>
                    <dl class="assignment-submission-info">
                        <div>
                            <dt>Nama Berkas</dt>
                            <dd>{{ $ownSubmission->original_name }}</dd>
                        </div>
                        <div>
                            <dt>Waktu Pengumpulan</dt>
                            <dd>{{ $ownSubmission->submitted_at?->format('d M Y, H:i') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt>Status</dt>
                            <dd>{{ $ownSubmission->is_late ? 'Terlambat' : 'Tepat waktu' }}</dd>
                        </div>
                        @if ($ownSubmission->note)
                            <div>
                                <dt>Catatan</dt>
                                <dd>{{ $ownSubmission->note }}</dd>
                            </div>
                        @endif
                    </dl>

                    @if ($ownSubmission->grade)
                        <div class="assignment-grade-result">
                            <h3>Hasil Penilaian</h3>
                            <p>
                                Nilai:
                                <strong>{{ $ownSubmission->grade->score }} / {{ $assignment->max_score }}</strong>
                            </p>
                            @if ($ownSubmission->grade->feedback)
                                <p>{{ $ownSubmission->grade->feedback }}</p>
                            @endif
                        </div>
                    @else
                        <p class="assignment-pending-grade">Berkas sudah dikumpulkan dan menunggu penilaian.</p>
                    @endif

                    @if (auth()->user()->can('create', [\App\Models\Submission::class, $assignment]))
                        <div class="assignment-resubmit">
                            <h3>Kirim Ulang Tugas</h3>
                            <p>Pengumpulan ulang mengganti berkas sebelumnya.</p>
                            @if ($errors->any())
                                <div class="form-error assignment-submit-errors" role="alert">
                                    <strong>Tugas belum dapat dikumpulkan.</strong>
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <form
                                action="{{ route('mahasiswa.courses.assignments.submissions.store', [
                                    'course' => $assignment->course,
                                    'assignment' => $assignment,
                                ]) }}"
                                method="POST"
                                enctype="multipart/form-data"
                                class="assignment-submit-form"
                            >
                                @csrf
                                <div class="form-group">
                                    <label for="submission-file" class="form-label">Berkas baru (PDF, DOC, atau DOCX, maksimal 5 MB)</label>
                                    <input
                                        id="submission-file"
                                        name="file"
                                        type="file"
                                        class="form-input"
                                        accept=".pdf,.doc,.docx"
                                        required
                                    >
                                    @error('file')
                                        <small class="form-error">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="submission-note" class="form-label">Catatan (opsional)</label>
                                    <textarea
                                        id="submission-note"
                                        name="note"
                                        class="form-input"
                                        rows="3"
                                        maxlength="5000"
                                    >{{ old('note') }}</textarea>
                                    @error('note')
                                        <small class="form-error">{{ $message }}</small>
                                    @enderror
                                </div>
                                <button type="submit" class="primary-btn">Kirim Ulang Tugas</button>
                            </form>
                        </div>
                    @endif
                </section>
            @elseif (auth()->user()->can('create', [\App\Models\Submission::class, $assignment]))
                <section class="assignment-submit-section">
                    <h2>Kumpulkan Tugas</h2>
                    @if ($errors->any())
                        <div class="form-error assignment-submit-errors" role="alert">
                            <strong>Tugas belum dapat dikumpulkan.</strong>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form
                        action="{{ route('mahasiswa.courses.assignments.submissions.store', [
                            'course' => $assignment->course,
                            'assignment' => $assignment,
                        ]) }}"
                        method="POST"
                        enctype="multipart/form-data"
                        class="assignment-submit-form"
                    >
                        @csrf
                        <div class="form-group">
                            <label for="submission-file" class="form-label">Berkas (PDF, DOC, atau DOCX, maksimal 5 MB)</label>
                            <input
                                id="submission-file"
                                name="file"
                                type="file"
                                class="form-input"
                                accept=".pdf,.doc,.docx"
                                required
                            >
                            @error('file')
                                <small class="form-error">{{ $message }}</small>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="submission-note" class="form-label">Catatan (opsional)</label>
                            <textarea
                                id="submission-note"
                                name="note"
                                class="form-input"
                                rows="3"
                                maxlength="5000"
                            >{{ old('note') }}</textarea>
                            @error('note')
                                <small class="form-error">{{ $message }}</small>
                            @enderror
                        </div>
                        <button type="submit" class="primary-btn">Kirim Tugas</button>
                    </form>
                </section>
            @else
                <p class="assignment-pending-grade">Pengumpulan belum tersedia untuk tugas atau mata kuliah ini.</p>
            @endif
        @endif

        <footer class="assignment-detail-footer">
            <a
                href="{{ route($role . '.courses.assignments.index', [
                    'course' => $assignment->course,
                    'as' => $role,
                ]) }}"
                class="secondary-btn"
            >
                Kembali ke Daftar Tugas
            </a>
        </footer>
    </article>
</x-layout>
