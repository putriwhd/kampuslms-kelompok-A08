<x-layout>
    <x-slot:title>Detail Pengguna</x-slot:title>

    <div class="content-card">
        <h1>Detail Pengguna</h1>

        <table class="course-detail-table">
            <tbody>
                <tr>
                    <th>Nama</th>
                    <td>{{ $user->name }}</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ $user->email }}</td>
                </tr>
                <tr>
                    <th>NIM/NIP</th>
                    <td>{{ $user->nim_nip ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Role</th>
                    <td>{{ ucfirst($user->role) }}</td>
                </tr>
                <tr>
                    <th>Terdaftar</th>
                    <td>{{ $user->created_at?->format('d M Y H:i') ?? '-' }}</td>
                </tr>
            </tbody>
        </table>

        <div style="margin-top: 20px;">
            <a href="{{ route('users.index', ['as' => request('as', 'admin')]) }}">&larr; Kembali</a>
            |
            <a href="{{ route('users.edit', [$user, 'as' => request('as', 'admin')]) }}">Edit</a>
        </div>
    </div>
</x-layout>
