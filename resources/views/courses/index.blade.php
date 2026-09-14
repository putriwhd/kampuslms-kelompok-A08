<x-layout>
    <x-slot:title>Daftar Mata Kuliah</x-slot:title>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Daftar Mata Kuliah</h2>

        @if ($role === 'admin')
            <a href="{{ route('courses.create', ['as' => $role]) }}"
               style="background: #2563eb; color: white; padding: 10px 16px; border-radius: 6px; text-decoration: none;">
                + Tambah Mata Kuliah
            </a>
        @endif
    </div>

    <p style="margin-bottom: 20px;">
        @if ($role === 'admin')
            Sebagai Admin, Anda dapat mengelola data mata kuliah.
        @elseif ($role === 'dosen')
            Sebagai Dosen, Anda dapat melihat daftar mata kuliah.
        @else
            Sebagai Mahasiswa, Anda dapat melihat daftar mata kuliah yang tersedia.
        @endif
    </p>

    @if (session('success'))
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
            @forelse ($courses as $course)
                <tr>
                    <td>{{ $course->code }}</td>
                    <td>{{ $course->name ?? $course->title }}</td>
                    <td>{{ $course->lecturer->name ?? '-' }}</td>
                    <td>
                        <a href="{{ route('courses.show', [$course->id, 'as' => $role]) }}">Lihat</a>

                        @if ($role === 'admin')
                            |
                            <a href="{{ route('courses.edit', [$course->id, 'as' => $role]) }}">Edit</a>
                            |
                            <form action="{{ route('courses.destroy', [$course->id, 'as' => $role]) }}"
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

    <div style="margin-top: 20px;">
        {{ $courses->appends(['as' => $role])->links() }}
    </div>
</x-layout>
