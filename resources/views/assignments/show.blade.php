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
                                    {{ $row['submission_status'] === 'submitted' ? 'Sudah mengumpulkan' : 'Belum mengumpulkan' }}
                                </td>
                                <td>{{ $row['submitted_at']?->format('d M Y H:i') ?? '-' }}</td>
                                <td>
                                    {{ $row['submission_status'] === 'not_submitted' ? '-' : ($row['is_late'] ? 'Terlambat' : 'Tepat waktu') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">Belum ada mahasiswa yang terdaftar di kelas ini.</td>
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
                        @if ($submission->note)
                            <p>Catatan: {{ $submission->note }}</p>
                        @endif

                        @if ($submission->grade)
                            <p>
                                Nilai:
                                <strong>{{ number_format((float) $submission->grade->score, 2) }}</strong>
                                / {{ $assignment->max_score }}
                            </p>
                            @if ($submission->grade->feedback)
                                <p>Umpan balik: {{ $submission->grade->feedback }}</p>
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
                                <label for="score-{{ $submission->id }}">Nilai (maks. {{ $assignment->max_score }})</label>
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
                                <label for="feedback-{{ $submission->id }}">Umpan balik</label>
                                <textarea id="feedback-{{ $submission->id }}" name="feedback" rows="3">{{ old('feedback', $submission->grade?->feedback) }}</textarea>
                                <button type="submit">Simpan Nilai</button>
                            </form>
                        @endcan
                    </article>
                @empty
                    <p>Belum ada mahasiswa yang mengumpulkan tugas ini.</p>
                @endforelse
            </section>
        @elseif ($role === 'mahasiswa')
            <section class="student-grade-card" aria-labelledby="student-grade-heading">
                <h2 id="student-grade-heading">Nilai Saya</h2>
                @if ($ownSubmission?->grade)
                    <p>
                        Nilai:
                        <strong>{{ number_format((float) $ownSubmission->grade->score, 2) }}</strong>
                        / {{ $assignment->max_score }}
                    </p>
                    @if ($ownSubmission->grade->feedback)
                        <p>Umpan balik dosen: {{ $ownSubmission->grade->feedback }}</p>
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
            ]) }}">Kembali ke Daftar Tugas</a>
        </p>
    </article>
</x-layout>
