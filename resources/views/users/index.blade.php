<x-layout>
    <x-slot:title>Data Pengguna</x-slot:title>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2>Data Pengguna</h2>

        <a href="{{ route('users.create', ['as' => request('as', 'admin')]) }}"
           style="background: #2563eb; color: white; padding: 10px 16px; border-radius: 6px; text-decoration: none;">
            + Tambah Pengguna
        </a>
    </div>

    @if(session('success'))
        <div style="background: #dcfce7; color: #15803d; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="background: #fee2e2; color: #b91c1c; padding: 12px; border-radius: 6px; margin-bottom: 16px;">
            {{ session('error') }}
        </div>
    @endif

    <table border="1" cellpadding="10" cellspacing="0"
           style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="background: #f3f4f6;">
                <th>Nama</th>
                <th>Email</th>
                <th>NIM/NIP</th>
                <th>Role</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse($users as $user)
                <tr>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->nim_nip ?? '-' }}</td>
                    <td>{{ $user->role }}</td>
                    <td>
                        <a href="{{ route('users.show', [$user->id, 'as' => request('as', 'admin')]) }}">
                            Lihat
                        </a>

                        |

                        <a href="{{ route('users.edit', [$user->id, 'as' => request('as', 'admin')]) }}">
                            Edit
                        </a>

                        |

                        <form action="{{ route('users.destroy', [$user->id, 'as' => request('as', 'admin')]) }}"
                              method="POST"
                              style="display: inline;"
                              onsubmit="return confirm('Yakin ingin menghapus pengguna ini?')">

                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    style="color: red; background: none; border: none; cursor: pointer;">
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">
                        Belum ada data pengguna.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        {{ $users->links() }}
    </div>
</x-layout>