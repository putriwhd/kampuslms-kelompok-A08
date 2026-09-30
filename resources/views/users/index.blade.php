<x-layout>
    <x-slot:title>Data Pengguna</x-slot:title>

    @php
        $role = request('as', 'admin');
        $currentRole = $role;
    @endphp

    <div class="user-page-header">
        <div>
            <h1>Data Pengguna</h1>
            <p>Kelola data pengguna yang terdaftar di KampusLMS.</p>
        </div>

        @if ($role === 'admin')
            <a href="{{ route('admin.users.create', ['as' => $role]) }}" class="user-add-button">
                + Tambah Pengguna
            </a>
        @endif
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

        {{-- FORM PENCARIAN & FILTER --}}
        <div class="user-filter-wrapper">
            <form action="{{ route('admin.users.index') }}" method="GET" class="user-filter-form">
                <input type="hidden" name="as" value="{{ $role }}">

                <div class="filter-inputs">
                    <input
                        type="text"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari nama, email, atau NIM/NIP..."
                        class="filter-input"
                    >

                    <select name="role" class="filter-select">
                        <option value="">Semua Role</option>
                        <option value="admin" @selected(request('role') === 'admin')>Admin</option>
                        <option value="dosen" @selected(request('role') === 'dosen')>Dosen</option>
                        <option value="mahasiswa" @selected(request('role') === 'mahasiswa')>Mahasiswa</option>
                    </select>

                    <button type="submit" class="btn-search">Cari</button>
                    @if(request('search') || request('role'))
                        <a href="{{ route('admin.users.index', ['as' => $role]) }}" class="btn-reset">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
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
                                    {{ $user->nim_nip ?? $user->identity_number ?? '-' }}
                                </span>
                            </td>

                            <td>
                                <span class="user-role-badge">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>

                            <td class="user-actions">
                                <a href="{{ route('admin.users.show', [$user->id, 'as' => $role]) }}" class="action-view">
                                    Lihat
                                </a>

                                @if ($role === 'admin')
                                    <a href="{{ route('admin.users.edit', [$user->id, 'as' => $role]) }}" class="action-edit">
                                        Edit
                                    </a>

                                    <form action="{{ route('admin.users.destroy', [$user->id, 'as' => $role]) }}"
                                          method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus pengguna ini?')"
                                          style="display: inline;">

                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="action-delete">
                                            Hapus
                                        </button>
                                    </form>
                                @endif
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
        {{ $users->appends(request()->query())->links() }}
    </div>

    <style>
        .user-page-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 24px;
        }

        .user-page-header h1 {
            margin: 0 0 6px;
            color: #2b3674;
            font-size: 28px;
            font-weight: 700;
        }

        .user-page-header p {
            margin: 0;
            color: #8f9bba;
            font-size: 14px;
        }

        .user-add-button {
            display: inline-flex;
            align-items: center;
            padding: 10px 18px;
            background: #7592e6;
            color: #ffffff;
            border-radius: 10px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }

        .user-table-card {
            overflow: hidden;
            background: #ffffff;
            border: 1px solid #e0e7ff;
            border-radius: 14px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
        }

        .user-table-header {
            padding: 24px 28px;
            background: #ffffff;
        }

        .user-table-header h2 {
            margin: 0 0 6px;
            color: #2b3674;
            font-size: 18px;
            font-weight: 700;
        }

        .user-table-header p {
            margin: 0;
            color: #8f9bba;
            font-size: 13px;
        }

        .user-filter-wrapper {
            padding: 16px 28px;
            background: #ffffff;
            border-top: 1px solid #f4f7fe;
            border-bottom: 1px solid #f4f7fe;
        }

        .filter-inputs {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .filter-input {
            flex: 1;
            min-width: 200px;
            padding: 10px 16px;
            border: 1px solid #e0e7ff;
            border-radius: 8px;
            font-size: 13px;
            outline: none;
            background: #ffffff;
            color: #2b3674;
        }

        .filter-select {
            padding: 10px 16px;
            border: 1px solid #e0e7ff;
            border-radius: 8px;
            font-size: 13px;
            outline: none;
            background: #ffffff;
            color: #2b3674;
            cursor: pointer;
        }

        .btn-search {
            padding: 10px 20px;
            background: #335cff;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-reset {
            padding: 10px 18px;
            background: #f4f7fe;
            color: #2b3674;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }

        .user-table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        .user-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .user-table thead {
            background: #f8fafc;
        }

        .user-table th {
            padding: 16px 28px;
            color: #2b3674;
            font-size: 12px;
            font-weight: 700;
            text-align: left;
            border-bottom: 1px solid #f4f7fe;
            white-space: nowrap;
        }

        .user-table td {
            padding: 18px 28px;
            color: #2b3674;
            border-bottom: 1px solid #f4f7fe;
            vertical-align: middle;
        }

        .user-table tbody tr:last-child td {
            border-bottom: none;
        }

        .user-name {
            color: #2b3674;
            font-weight: 700;
        }

        .user-email, .user-nim {
            color: #2b3674;
        }

        .user-role-badge {
            display: inline-flex;
            padding: 6px 14px;
            background: #f4f7fe;
            color: #2b3674;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
        }

        .user-actions {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .user-actions form {
            margin: 0;
        }

        .action-view, .action-edit {
            padding: 6px 14px;
            background: #f4f7fe;
            color: #2b3674;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
        }

        .action-delete {
            padding: 6px 14px;
            background: #ffe2e2;
            color: #ee5d50;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }

        .user-empty {
            padding: 40px 20px !important;
            text-align: center;
            color: #8f9bba !important;
        }

        .user-pagination {
            margin-top: 20px;
        }

        .user-success {
            margin-bottom: 20px;
            padding: 12px 16px;
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            font-size: 14px;
        }

        .user-error {
            margin-bottom: 20px;
            padding: 12px 16px;
            background: #fef2f2;
            color: #ee5d50;
            border: 1px solid #fecaca;
            border-radius: 8px;
            font-size: 14px;
        }
    </style>
</x-layout>