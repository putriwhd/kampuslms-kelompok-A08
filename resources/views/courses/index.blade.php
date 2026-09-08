{{--
    Tabel hanya berisi 4 kolom sesuai instruksi soal: kode, nama, SKS,
    dosen -- deskripsi sengaja TIDAK ditampilkan di sini, cukup di
    halaman show (detail).
--}}
<x-layout>
    <x-slot:title>
        Daftar Mata Kuliah
    </x-slot:title>

    <h1>Daftar Mata Kuliah</h1>

    {{--
        @forelse dipakai supaya ada fallback rapi kalau $courses
        kosong, tanpa if-else manual di luar tabel.
    --}}
    <table border="1" cellpadding="8">
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
                    {{-- {{ }} dipakai di semua output (bukan {!! !!})
                         supaya otomatis di-escape -- pertahanan XSS
                         sesuai Bagian 2.1 modul. --}}
                    <td>{{ $item['kode'] }}</td>
                    <td>{{ $item['nama'] }}</td>
                    <td>{{ $item['sks'] }}</td>
                    <td>{{ $item['dosen'] }}</td>
                    <td>
                        {{--
                            route('courses.show', $item['id']) dipakai,
                            BUKAN "/courses/{$item['id']}" hardcode --
                            supaya path bisa berubah tanpa menyisir
                            semua view (lihat Bagian 2.1 modul).
                        --}}
                        <a href="{{ route('courses.show', $item['id']) }}">
                            Lihat Detail
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Belum ada mata kuliah.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</x-layout>