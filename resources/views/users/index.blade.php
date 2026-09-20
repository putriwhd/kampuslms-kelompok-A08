<x-layout>
    <x-slot:title>Data Pengguna</x-slot:title>

    <div class="user-page-header">
        <div>
            <h1>Data Pengguna</h1>
            <p>Kelola data pengguna yang terdaftar di KampusLMS.</p>
        </div>

        <a href="{{ route('users.create', ['as' => request('as', 'admin')]) }}"
           class="user-add-button">
            + Tambah Pengguna
        </a>
    </div>

    @if (session('success'))
        <div class="user-success">
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="user-error">
            {{ session('error') }}
        </div>
    @endif

    <div class="user-table-card">

        <div class="user-table-header">
            <div>
                <h2>Daftar Pengguna</h2>
                <p>Informasi pengguna berdasarkan nama, email, NIM/NIP, dan role.</p>
            </div>
        </div>

        <div class="user-table-wrapper">
            <table class="user-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>NIM/NIP</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <span class="user-name">
                                    {{ $user->name }}
                                </span>
                            </td>

                            <td>
                                <span class="user-email">
                                    {{ $user->email }}
                                </span>
                            </td>

                            <td>
                                <span class="user-nim">
                                    {{ $user->nim_nip ?? '-' }}
                                </span>
                            </td>

                            <td>
                                <span class="user-role">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>

                            <td class="user-actions">

                                <a href="{{ route('users.show', [$user->id, 'as' => request('as', 'admin')]) }}"
                                   class="action-view">
                                    Lihat
                                </a>

                                <a href="{{ route('users.edit', [$user->id, 'as' => request('as', 'admin')]) }}"
                                   class="action-edit">
                                    Edit
                                </a>

                                <form action="{{ route('users.destroy', [$user->id, 'as' => request('as', 'admin')]) }}"
                                      method="POST"
                                      onsubmit="return confirm('Yakin ingin menghapus pengguna ini?')">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="action-delete">
                                        Hapus
                                    </button>
                                </form>

                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="5" class="user-empty">
                                Belum ada data pengguna.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="user-pagination">
        {{ $users->links() }}
    </div>


    <style>
        .user-page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 20px;
            margin-bottom: 28px;
        }

        .user-page-header h1 {
            margin: 0 0 8px;
            color: var(--blue-dark);
            font-size: 27px;
            font-weight: 700;
        }

        .user-page-header p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .user-add-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 16px;
            background: var(--blue);
            color: var(--white);
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
        }

        .user-add-button:hover {
            background: var(--blue-dark);
        }

        .user-success {
            margin-bottom: 20px;
            padding: 12px 16px;
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            font-size: 14px;
        }

        .user-error {
            margin-bottom: 20px;
            padding: 12px 16px;
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
            border-radius: 8px;
            font-size: 14px;
        }

        .user-table-card {
            overflow: hidden;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .user-table-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
        }

        .user-table-header h2 {
            margin: 0 0 5px;
            color: var(--blue-dark);
            font-size: 18px;
            font-weight: 700;
        }

        .user-table-header p {
            margin: 0;
            color: var(--muted);
            font-size: 13px;
        }

        .user-table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        .user-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .user-table thead {
            background: var(--blue-soft);
        }

        .user-table th {
            padding: 14px 20px;
            color: var(--blue-dark);
            font-size: 13px;
            font-weight: 700;
            text-align: left;
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        .user-table td {
            padding: 15px 20px;
            color: var(--text);
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        .user-table tbody tr:last-child td {
            border-bottom: none;
        }

        .user-table tbody tr {
            transition: background 0.15s ease;
        }

        .user-table tbody tr:hover {
            background: #f8fafc;
        }

        .user-name {
            color: var(--blue-dark);
            font-weight: 600;
        }

        .user-email {
            color: var(--text);
        }

        .user-nim {
            color: var(--muted);
            font-size: 13px;
        }

        .user-role {
            display: inline-flex;
            padding: 5px 10px;
            background: var(--blue-soft);
            color: var(--blue-dark);
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .user-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            min-width: 180px;
        }

        .user-actions form {
            margin: 0;
        }

        .action-view,
        .action-edit,
        .action-delete {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 11px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .action-view {
            background: var(--blue-soft);
            color: var(--blue-dark);
        }

        .action-edit {
            background: #f3f4f6;
            color: #555;
        }

        .action-delete {
            background: #fef2f2;
            color: #dc2626;
            border: none;
        }

        .action-view:hover,
        .action-edit:hover,
        .action-delete:hover {
            opacity: 0.8;
        }

        .user-empty {
            padding: 40px 20px !important;
            text-align: center;
            color: var(--muted) !important;
        }

        .user-pagination {
            margin-top: 20px;
        }

        @media (max-width: 700px) {
            .user-page-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .user-add-button {
                width: 100%;
            }

            .user-table th,
            .user-table td {
                padding: 12px 14px;
            }
        }
    </style>
</x-layout>