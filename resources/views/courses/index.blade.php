<x-layout>

    <x-slot:title>
        Daftar Mata Kuliah
    </x-slot:title>

    <div class="page-header">
        <h1 class="page-title">
            Daftar Mata Kuliah
        </h1>

        <p class="page-lead">
            Berikut adalah daftar mata kuliah yang tersedia di KampusLMS.
        </p>
    </div>

    <div class="table-wrapper">

        <table class="course-table">

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

                @forelse ($courses as $item)

                    <tr>
                        <td>{{ $item['kode'] }}</td>

                        <td>{{ $item['nama'] }}</td>

                        <td>{{ $item['sks'] }}</td>

                        <td>{{ $item['dosen'] }}</td>

                        <td>
                            <a
                                href="{{ route('courses.show', $item['id']) }}"
                                class="link-action"
                            >
                                Lihat Detail →
                            </a>
                        </td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="5">
                            Belum ada mata kuliah.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</x-layout> 