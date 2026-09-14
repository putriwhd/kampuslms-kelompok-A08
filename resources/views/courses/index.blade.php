<x-layout>
    <x-slot:title>Daftar Mata Kuliah</x-slot:title>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Daftar Mata Kuliah</h2>

        {{-- Tombol Tambah HANYA muncul jika role = admin --}}
        @if($role === 'admin')
            <a href="{{ route('courses.create', ['as' => $role]) }}" 
               style="background: #2563eb; color: white; padding: 10px 16px; border-radius: 6px; text-decoration: none;">
                + Tambah Mata Kuliah
            </a>
        @endif
    </div>

    {{-- Pesan Sukses setelah Create/Update/Delete --}}
    @if(session('success'))
        <div style="background: #dcfce7; color: #15803d; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
            {{ session('success') }}
        </div>
    @endif

    <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f3f4f6;">
                <th>Kode</th>
                <th>Nama Mata Kuliah</th>
                <th>Dosen Pengampu</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($courses as $course)
                <tr>
                    <td>{{ $course->code }}</td>
                    <td>{{ $course->name }}</td>
                    <td>{{ $course->lecturer->name ?? '-' }}</td>
                    <td>
                        {{-- READ: Detail (Bisa diakses semua role) --}}
                        <a href="{{ route('courses.show', [$course->id, 'as' => $role]) }}">Lihat</a>

                        {{-- EDIT & DELETE: Hanya untuk Admin --}}
                        @if($role === 'admin')
                            | <a href="{{ route('courses.edit', [$course->id, 'as' => $role]) }}">Edit</a>

                            | <form action="{{ route('courses.destroy', [$course->id, 'as' => $role]) }}" 
                                    method="POST" style="display: inline;" 
                                    onsubmit="return confirm('Yakin ingin menghapus?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" style="color: red; background: none; border: none; cursor: pointer;">
                                    Hapus
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="4" style="text-align: center;">Belum ada data mata kuliah.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</x-layout>

            </thead>

            <tbody>

                @forelse ($courses as $item)

                    <tr>

                      <td>{{ $item['kode'] }}</td>

                        <td>{{!!$item['nama']}}</td>

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

