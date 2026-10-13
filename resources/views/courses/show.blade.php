<x-layout>
    <x-slot:title>
        {{ $course->name }}
    </x-slot:title>

    <div class="course-detail-card">
        <div class="course-detail-header">
            <h1>{{ $course->name }}</h1>
            <p>Detail informasi mata kuliah</p>
        </div>

        <table class="course-detail-table">
            <tbody>
                <tr>
                    <th>Kode</th>
                    <td>{{ $course->code }}</td>
                </tr>

                <tr>
                    <th>Dosen Pengampu</th>
                    <td>{{ $course->lecturer?->name ?? '-' }}</td>
                </tr>

                <tr>
                    <th>SKS</th>
                    <td>{{ $course->sks }}</td>
                </tr>

                <tr>
                    <th>Status</th>
                    <td>
                        <span class="course-status-badge">
                            {{ ucfirst($course->status ?? 'draft') }}
                        </span>
                    </td>
                </tr>

                <tr>
                    <th>Deskripsi</th>
                    <td>{{ $course->description ?: '-' }}</td>
                </tr>
            </tbody>
        </table>

        <div class="course-detail-footer">
            <p>
                @can('viewAny', \App\Models\Material::class)
                    <a href="{{ route($role . '.courses.materials.index', [
                        'course' => $course,
                        'as' => $role,
                    ]) }}">
                        Lihat Materi
                    </a>
                @endcan

                @can('viewAny', \App\Models\Assignment::class)
                    |
                    <a href="{{ route($role . '.courses.assignments.index', [
                        'course' => $course,
                        'as' => $role,
                    ]) }}">
                        Lihat Tugas
                    </a>
                @endcan
            </p>

            <a
                href="{{ route($role . '.courses.index', ['as' => $role]) }}"
                class="course-back-button"
            >
                &larr; Kembali ke Daftar Mata Kuliah
            </a>
        </div>

        @if ($role === 'admin')
            <section class="course-enrollment-card" aria-labelledby="enrollment-heading">
                <h2 id="enrollment-heading">Pendaftaran Mahasiswa</h2>
                <p>Pilih mahasiswa yang terdaftar pada mata kuliah ini. Menyimpan pilihan akan menggantikan daftar pendaftaran saat ini.</p>

                <form
                    action="{{ route('admin.courses.students.update', [
                        'course' => $course,
                        'as' => 'admin',
                    ]) }}"
                    method="POST"
                >
                    @csrf
                    @method('PUT')

                    @if ($students->isEmpty())
                        <p>Belum ada akun mahasiswa. Tambahkan akun mahasiswa terlebih dahulu.</p>
                    @else
                        <fieldset class="student-enrollment-list">
                            <legend>Mahasiswa yang terdaftar</legend>
                            @foreach ($students as $student)
                                <label class="student-enrollment-option">
                                    <input
                                        type="checkbox"
                                        name="student_ids[]"
                                        value="{{ $student->id }}"
                                        @checked(in_array($student->id, $enrolledStudentIds, true))
                                    >
                                    <span>
                                        {{ $student->name }}
                                        <small>{{ $student->email }}{{ $student->nim_nip ? ' · '.$student->nim_nip : '' }}</small>
                                    </span>
                                </label>
                            @endforeach
                        </fieldset>

                        <button type="submit" class="primary-btn">Simpan Pendaftaran</button>
                    @endif
                </form>
            </section>
        @endif
    </div>
</x-layout>