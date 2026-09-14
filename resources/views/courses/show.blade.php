<x-layout>

    <x-slot:title>
        {{ $course['nama'] }}
    </x-slot:title>

    <div class="course-detail-card">

        <div class="course-detail-header">

            <h1>{{ $course['nama'] }}</h1>

            <p>
                Detail informasi mata kuliah
            </p>

        </div>


        {{-- Informasi Mata Kuliah --}}
        <table class="course-detail-table">

            <tbody>

                <tr>
                    <th>Kode</th>
                    <td>{{ $course['kode'] }}</td>
                </tr>

                <tr>
                    <th>SKS</th>
                    <td>{{ $course['sks'] }}</td>
                </tr>

                <tr>
                    <th>Dosen Pengampu</th>
                    <td>{{ $course['dosen'] }}</td>
                </tr>

                <tr>
                    <th>Deskripsi</th>
                    <td>{{ $course['deskripsi'] }}</td>
                </tr>

            </tbody>

        </table>


        {{-- Tombol Kembali --}}
        <div class="course-detail-footer">

            <a
                href="{{ route('courses.index') }}"
                class="back-link"
            >
                &larr; Kembali ke Daftar Mata Kuliah
            </a>

        </div>

    </div>

</x-layout>