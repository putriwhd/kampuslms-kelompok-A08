<x-layout>

    <x-slot:title>
        {{ $course->name }}
    </x-slot:title>

    <div
        style="
            max-width: 900px;
            margin: 20px auto;
            background: #ffffff;
            border: 1px solid #d8e4f2;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(79, 111, 159, 0.10);
            overflow: hidden;
        "
    >

        {{-- HEADER --}}
        <div
            style="
                padding: 28px 32px;
                background: #dceafa;
                border-bottom: 1px solid #d8e4f2;
            "
        >

            <h1>{{ $course->name }}</h1>

            <p
                style="
                    margin: 0;
                    color: #66758a;
                    font-size: 14px;
                "
            >
                Detail informasi mata kuliah
            </p>

        </div>


        {{-- INFORMASI MATA KULIAH --}}
        <table
            style="
                width: 100%;
                border-collapse: collapse;
                font-size: 14px;
            "
        >

            <tbody>

                <tr>
                    <th>Kode</th>
                    <td>{{ $course->code }}</td>
                </tr>

                <tr>
                    <th>Dosen Pengampu</th>
                    <td>{{ $course->lecturer->name ?? '-' }}</td>
                </tr>

                <tr>
                    <th>SKS</th>
                    <td>{{ $course->sks }}</td>
                </tr>

                <tr>
                    <th>Status</th>
                    <td>{{ $course->status }}</td>
                </tr>


                <tr>
                    <th>Deskripsi</th>
                    <td>{{ $course->description ?? '-' }}</td>
                </tr>

            </tbody>

        </table>


        {{-- FOOTER --}}
        <div
            style="
                padding: 18px 24px;
                background: #f7f9fc;
                border-top: 1px solid #d8e4f2;
            "
        >

            <a
                href="{{ route('courses.index', ['as' => $role ?? request('as', 'mahasiswa')]) }}"
                class="back-link"
            >
                &larr; Kembali ke Daftar Mata Kuliah
            </a>

        </div>

    </div>

</x-layout>