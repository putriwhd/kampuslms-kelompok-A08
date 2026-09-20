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

<<<<<<< HEAD
            <h1
                style="
                    margin: 0 0 6px 0;
                    color: #4f6f9f;
                    font-size: 25px;
                    font-weight: 700;
                "
            >
                {{ $course->name }}
            </h1>
=======
            <h1>{{ $course->name }}</h1>
>>>>>>> 36082a2b9c28c40225d5f613702da30cc2679637

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
<<<<<<< HEAD
                    <th
                        style="
                            width: 200px;
                            padding: 17px 24px;
                            text-align: left;
                            background: #f7f9fc;
                            color: #4f6f9f;
                            font-weight: 700;
                            border-bottom: 1px solid #d8e4f2;
                        "
                    >
                        Kode
                    </th>

                    <td
                        style="
                            padding: 17px 24px;
                            color: #34445a;
                            border-bottom: 1px solid #d8e4f2;
                        "
                    >
                        {{ $course->code }}
                    </td>
                </tr>


                <tr>
                    <th
                        style="
                            padding: 17px 24px;
                            text-align: left;
                            background: #f7f9fc;
                            color: #4f6f9f;
                            font-weight: 700;
                            border-bottom: 1px solid #d8e4f2;
                        "
                    >
                        Dosen Pengampu
                    </th>

                    <td
                        style="
                            padding: 17px 24px;
                            color: #34445a;
                            border-bottom: 1px solid #d8e4f2;
                        "
                    >
                        {{ $course->lecturer->name ?? '-' }}
                    </td>
=======
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
>>>>>>> 36082a2b9c28c40225d5f613702da30cc2679637
                </tr>


                <tr>
<<<<<<< HEAD
                    <th
                        style="
                            padding: 17px 24px;
                            text-align: left;
                            background: #f7f9fc;
                            color: #4f6f9f;
                            font-weight: 700;
                            border-bottom: 1px solid #d8e4f2;
                        "
                    >
                        SKS
                    </th>

                    <td
                        style="
                            padding: 17px 24px;
                            color: #34445a;
                            border-bottom: 1px solid #d8e4f2;
                        "
                    >
                        {{ $course->sks }}
                    </td>
                </tr>


                <tr>
                    <th
                        style="
                            padding: 17px 24px;
                            text-align: left;
                            background: #f7f9fc;
                            color: #4f6f9f;
                            font-weight: 700;
                            border-bottom: 1px solid #d8e4f2;
                        "
                    >
                        Status
                    </th>

                    <td
                        style="
                            padding: 17px 24px;
                            color: #34445a;
                            border-bottom: 1px solid #d8e4f2;
                        "
                    >
                        <span
                            style="
                                display: inline-block;
                                padding: 5px 11px;
                                background: #eaf6ee;
                                color: #39805a;
                                border-radius: 6px;
                                font-size: 12px;
                                font-weight: 600;
                            "
                        >
                            {{ $course->status }}
                        </span>
                    </td>
                </tr>


                <tr>
                    <th
                        style="
                            padding: 17px 24px;
                            text-align: left;
                            vertical-align: top;
                            background: #f7f9fc;
                            color: #4f6f9f;
                            font-weight: 700;
                        "
                    >
                        Deskripsi
                    </th>

                    <td
                        style="
                            padding: 17px 24px;
                            color: #34445a;
                            line-height: 1.6;
                        "
                    >
                        {{ $course->description ?? '-' }}
                    </td>
=======
                    <th>Deskripsi</th>
                    <td>{{ $course->description ?? '-' }}</td>
>>>>>>> 36082a2b9c28c40225d5f613702da30cc2679637
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
<<<<<<< HEAD
                style="
                    display: inline-block;
                    padding: 9px 15px;
                    background: #7fa6d8;
                    color: #ffffff;
                    border-radius: 7px;
                    text-decoration: none;
                    font-size: 13px;
                    font-weight: 600;
                "
=======
                class="back-link"
>>>>>>> 36082a2b9c28c40225d5f613702da30cc2679637
            >
                &larr; Kembali ke Daftar Mata Kuliah
            </a>

        </div>

    </div>

</x-layout>