<x-layout>
    <x-slot:title>Data Pengguna</x-slot:title>

    {{-- HEADER --}}
    <div class="user-header">
        <div>
            <h1>Data Pengguna</h1>
            <p>Kelola data pengguna sistem KampusLMS.</p>
        </div>

        <a href="{{ route('users.create', ['as' => request('as', 'admin')]) }}" class="btn-add">
            <span>+</span>
            Tambah Pengguna
        </a>
    </div>

    {{-- ALERT MESSAGES --}}
    @if (session('success'))
        <div class="alert-success">
            <span>✓</span>
            {{ session('success') }}
        </div>
    @endif

    @if (session('error'))
        <div class="alert-error">
            <span>✕</span>
            {{ session('error') }}
        </div>
    @endif

    {{-- CARD TABEL --}}
    <div class="user-card">

        {{-- CARD HEADER --}}
        <div class="user-card-header">
            <div>
                <h2>Data Pengguna Sistem</h2>
                <p>Daftar seluruh akun yang terdaftar dalam sistem KampusLMS.</p>
            </div>

            <div class="user-count">
                {{ $users->total() }} Pengguna
            </div>
        </div>

        {{-- TABLE --}}
        <div class="table-container">
            <table class="user-table">

                <thead>
                    <tr>
                        <th width="25%">Nama</th>
                        <th width="25%">Email</th>
                        <th width="20%">NIM/NIP</th>
                        <th width="15%" class="text-center">Role</th>
                        <th width="190px" class="text-center">Aksi</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse ($users as $user)

                        <tr>

                            {{-- NAMA --}}
                            <td>
                                <a href="{{ route('users.show', [$user->id, 'as' => request('as', 'admin')]) }}" class="user-name">
                                    {{ $user->name }}
                                </a>
                            </td>

                            {{-- EMAIL --}}
                            <td>
                                <span class="user-email">
                                    {{ $user->email }}
                                </span>
                            </td>

                            {{-- NIM / NIP --}}
                            <td>
                                <span class="user-identity">
                                    {{ $user->nim_nip ?? '-' }}
                                </span>
                            </td>

                            {{-- ROLE --}}
                            <td class="text-center">
                                @php
                                    $role = strtolower($user->role ?? 'mahasiswa');
                                @endphp

                                @if ($role === 'admin')
                                    <span class="role-badge role-admin">
                                        Admin
                                    </span>
                                @elseif ($role === 'dosen')
                                    <span class="role-badge role-dosen">
                                        Dosen
                                    </span>
                                @else
                                    <span class="role-badge role-mahasiswa">
                                        Mahasiswa
                                    </span>
                                @endif
                            </td>

                            {{-- AKSI --}}
                            <td>
                                <div class="action-buttons">

                                    <a href="{{ route('users.show', [$user->id, 'as' => request('as', 'admin')]) }}" class="btn-view">
                                        Lihat
                                    </a>

                                    <a href="{{ route('users.edit', [$user->id, 'as' => request('as', 'admin')]) }}" class="btn-edit">
                                        Edit
                                    </a>

                                    <form action="{{ route('users.destroy', [$user->id, 'as' => request('as', 'admin')]) }}"
                                          method="POST"
                                          onsubmit="return confirm('Yakin ingin menghapus pengguna ini?')">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn-delete">
                                            Hapus
                                        </button>
                                    </form>

                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="empty-data">

                                <div class="empty-icon">👤</div>

                                <strong>
                                    Belum ada data pengguna
                                </strong>

                                <p>
                                    Data pengguna sistem belum tersedia.
                                </p>

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>

    </div>

    {{-- PAGINATION --}}
    <div class="pagination">
        {{ $users->withQueryString()->links() }}
    </div>

    {{-- STYLE --}}
    <style>

        /* =========================
           HEADER
        ========================= */

        .user-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            gap: 24px;
            margin-bottom: 28px;
        }

        .user-header h1 {
            margin: 0 0 8px;
            color: var(--blue-dark);
            font-size: 27px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        .user-header p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
        }


        /* =========================
           BUTTON TAMBAH
        ========================= */

        .btn-add {
            display: inline-flex;
            align-items: center;
            gap: 8px;

            padding: 10px 16px;

            background: var(--blue);
            color: var(--white);

            border-radius: 8px;
            text-decoration: none;

            font-size: 13px;
            font-weight: 600;

            white-space: nowrap;

            transition: var(--transition);
        }

        .btn-add span {
            font-size: 18px;
            line-height: 1;
            font-weight: 400;
        }

        .btn-add:hover {
            background: var(--blue-dark);
            transform: translateY(-1px);
        }


        /* =========================
           ALERT
        ========================= */

        .alert-success,
        .alert-error {
            display: flex;
            align-items: center;
            gap: 10px;

            margin-bottom: 20px;
            padding: 12px 16px;

            border-radius: 8px;

            font-size: 14px;
        }

        .alert-success {
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .alert-error {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .alert-success span,
        .alert-error span {
            font-weight: 700;
        }


        /* =========================
           CARD
        ========================= */

        .user-card {
            overflow: hidden;

            background: var(--white);

            border: 1px solid var(--border);
            border-radius: var(--radius);

            box-shadow: var(--shadow);
        }


        /* =========================
           CARD HEADER
        ========================= */

        .user-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;

            gap: 20px;

            padding: 20px 24px;

            border-bottom: 1px solid var(--border);
        }

        .user-card-header h2 {
            margin: 0 0 5px;

            color: var(--blue-dark);

            font-size: 18px;
            font-weight: 700;
        }

        .user-card-header p {
            margin: 0;

            color: var(--muted);

            font-size: 13px;
        }

        .user-count {
            padding: 6px 11px;

            background: var(--blue-soft);
            color: var(--blue-dark);

            border-radius: 6px;

            font-size: 12px;
            font-weight: 600;

            white-space: nowrap;
        }


        /* =========================
           TABLE
        ========================= */

        .table-container {
            width: 100%;
            overflow-x: auto;
        }

        .user-table {
            width: 100%;

            border-collapse: collapse;

            font-size: 14px;
        }


        /* HEADER TABLE */

        .user-table thead {
            background: var(--blue-soft);
        }

        .user-table th {
            padding: 13px 20px;

            color: var(--blue-dark);

            font-size: 12px;
            font-weight: 700;

            text-align: left;

            border-bottom: 1px solid var(--border);

            white-space: nowrap;
        }

        .user-table th.text-center {
            text-align: center;
        }


        /* ISI TABLE */

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


        /* =========================
           DETAIL DATA PENGGUNA
        ========================= */

        .user-name {
            color: var(--blue-dark);

            font-weight: 600;

            text-decoration: none;

            transition: var(--transition);
        }

        .user-name:hover {
            color: var(--blue);
        }

        .user-email,
        .user-identity {
            color: var(--muted);
        }


        /* =========================
           ROLE BADGES
        ========================= */

        .role-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            min-width: 80px;

            padding: 5px 10px;

            border-radius: 6px;

            font-size: 11px;
            font-weight: 700;

            white-space: nowrap;
        }

        .role-admin {
            background: #fee2e2;
            color: #991b1b;
        }

        .role-dosen {
            background: #fef3c7;
            color: #92400e;
        }

        .role-mahasiswa {
            background: #e0f2fe;
            color: #075985;
        }


        /* =========================
           ACTION
        ========================= */

        .action-buttons {
            display: flex;
            justify-content: center;
            align-items: center;

            gap: 6px;
        }

        .action-buttons form {
            margin: 0;
        }

        .btn-view,
        .btn-edit,
        .btn-delete {
            display: inline-flex;
            align-items: center;
            justify-content: center;

            min-width: 50px;

            padding: 6px 10px;

            border-radius: 6px;

            font-size: 12px;
            font-weight: 600;

            text-decoration: none;

            cursor: pointer;

            transition: var(--transition);
        }


        /* LIHAT */

        .btn-view {
            background: var(--blue-soft);
            color: var(--blue-dark);
        }

        .btn-view:hover {
            background: var(--blue-light);
        }


        /* EDIT */

        .btn-edit {
            background: #f1f5f9;
            color: #475569;
        }

        .btn-edit:hover {
            background: #e2e8f0;
        }


        /* HAPUS */

        .btn-delete {
            background: #fef2f2;
            color: #dc2626;

            border: none;
        }

        .btn-delete:hover {
            background: #fee2e2;
        }


        /* =========================
           EMPTY DATA
        ========================= */

        .empty-data {
            padding: 50px 20px !important;

            text-align: center;

            color: var(--muted) !important;
        }

        .empty-icon {
            margin-bottom: 10px;

            font-size: 30px;
            opacity: 0.7;
        }

        .empty-data strong {
            display: block;

            margin-bottom: 4px;

            color: var(--text);

            font-size: 14px;
        }

        .empty-data p {
            margin: 0;

            font-size: 13px;
        }


        /* =========================
           PAGINATION
        ========================= */

        .pagination {
            margin-top: 20px;
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 700px) {

            .user-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .btn-add {
                width: 100%;
                justify-content: center;
            }

            .user-card-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .user-table th,
            .user-table td {
                padding: 12px 14px;
            }

            .action-buttons {
                justify-content: flex-start;
            }

        }

    </style>

</x-layout>