<x-layout>

    <x-slot:title>
        {{ $course->name }}
    </x-slot:title>

    <div class="course-detail-card">

        <div class="course-detail-header">
            <h1>{{ $course->name }}</h1>

            <p>
                Detail informasi mata kuliah
            </p>
        </div>

        <table class="course-detail-table">
            <tbody>

                <tr>
                    <th>Kode</th>

                    <td>
                        {{ $course->code }}
                    </td>
                </tr>

                <tr>
                    <th>Dosen Pengampu</th>

                    <td>
                        {{ $course->lecturer->name ?? '-' }}
                    </td>
                </tr>

                <tr>
                    <th>SKS</th>

                    <td>
                        {{ $course->sks }}
                    </td>
                </tr>

                <tr>
                    <th>Status</th>

                    <td>
                        <span class="course-status-badge">
                            {{ $course->status }}
                        </span>
                    </td>
                </tr>

                <tr>
                    <th>Deskripsi</th>

                    <td>
                        {{ $course->description ?? '-' }}
                    </td>
                </tr>

            </tbody>
        </table>

        <div class="course-detail-footer">

            <p>
                <a
                    href="{{ route($role . '.courses.materials.index', [$course, 'as' => $role]) }}"
                >
                    Lihat Materi
                </a>

                |

                <a
                    href="{{ route($role . '.courses.assignments.index', [$course, 'as' => $role]) }}"
                >
                    Lihat Tugas
                </a>
            </p>

            <a
                href="{{ route($role . '.courses.index', ['as' => $role]) }}"
                class="course-back-button"
            >
                &larr; Kembali ke Daftar Mata Kuliah
            </a>

        </div>

    </div>

</x-layout>