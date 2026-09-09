<x-layout title="Daftar Mata Kuliah">

    <h1>Daftar Mata Kuliah</h1>

    <p>
        Berikut adalah daftar mata kuliah KampusLMS.
    </p>

    <table border="1" cellpadding="10">

        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama Mata Kuliah</th>
                <th>SKS</th>
                <th>Dosen</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>

            @foreach ($courses as $course)

                <tr>

                    <td>
                        {{ $course['code'] }}
                    </td>

                    <td>
                        {{ $course['name'] }}
                    </td>

                    <td>
                        {{ $course['sks'] }}
                    </td>

                    <td>
                        {{ $course['lecturer'] }}
                    </td>

                    <td>
                        <a href="{{ route('courses.show', $course['id']) }}">
                            Lihat Detail
                        </a>
                    </td>

                </tr>

            @endforeach

        </tbody>

    </table>

</x-layout>