<x-layout>
    <x-slot:title>Detail Pengguna</x-slot:title>

    @php
        $role = request('as', 'admin');
    @endphp

    <div class="user-page-container">
        <p class="sub-heading-text">Informasi lengkap mengenai pengguna KampusLMS.</p>

        <div class="user-detail-card">
            <div class="card-top-header">
                <div>
                    <h1 class="user-name-title">{{ $user->name }}</h1>
                    <p class="user-sub-info">Informasi akun pengguna</p>
                </div>
                <span class="badge-role">{{ ucfirst($user->role) }}</span>
            </div>

            <div class="card-detail-table">
                <div class="table-row">
                    <div class="table-label">Nama</div>
                    <div class="table-value">{{ $user->name }}</div>
                </div>

                <div class="table-row">
                    <div class="table-label">Email</div>
                    <div class="table-value">{{ $user->email }}</div>
                </div>

                <div class="table-row">
                    <div class="table-label">NIM/NIP</div>
                    <div class="table-value">{{ $user->nim_nip ?? $user->identity_number ?? '-' }}</div>
                </div>

                <div class="table-row">
                    <div class="table-label">Role</div>
                    <div class="table-value">
                        <span class="badge-role">{{ ucfirst($user->role) }}</span>
                    </div>
                </div>

                <div class="table-row">
                    <div class="table-label">Terdaftar</div>
                    <div class="table-value">
                        {{ $user->created_at ? $user->created_at->format('d M Y H:i') : '-' }}
                    </div>
                </div>
            </div>

            <div class="card-bottom-actions">
                <a href="{{ route('admin.users.index', ['as' => $role]) }}" class="btn-kembali">
                    &larr; Kembali
                </a>
                <a href="{{ route('admin.users.edit', [$user->id, 'as' => $role]) }}" class="btn-edit-user">
                    Edit Pengguna
                </a>
            </div>
        </div>
    </div>

    <style>
        .user-page-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 10px 0;
        }

        .sub-heading-text {
            color: #8f9bba;
            font-size: 14px;
            margin-bottom: 24px;
        }

        .user-detail-card {
            background: #ffffff;
            border: 1px solid #e0e7ff;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.015);
            overflow: hidden;
        }

        .card-top-header {
            padding: 24px 28px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .user-name-title {
            margin: 0 0 6px 0;
            color: #2b3674;
            font-size: 20px;
            font-weight: 700;
        }

        .user-sub-info {
            margin: 0;
            color: #8f9bba;
            font-size: 13px;
        }

        .badge-role {
            background: #f4f7fe;
            color: #2b3674;
            padding: 6px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }

        .card-detail-table {
            border-top: 1px solid #f4f7fe;
            border-bottom: 1px solid #f4f7fe;
        }

        .table-row {
            display: flex;
            border-bottom: 1px solid #f4f7fe;
        }

        .table-row:last-child {
            border-bottom: none;
        }

        .table-label {
            width: 25%;
            padding: 18px 28px;
            background: #fcfdfe;
            color: #2b3674;
            font-size: 13px;
            font-weight: 700;
            display: flex;
            align-items: center;
        }

        .table-value {
            width: 75%;
            padding: 18px 28px;
            color: #2b3674;
            font-size: 13.5px;
            font-weight: 500;
            display: flex;
            align-items: center;
        }

        .card-bottom-actions {
            padding: 20px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #ffffff;
        }

        .btn-kembali {
            padding: 10px 18px;
            background: #f4f7fe;
            color: #2b3674;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: background 0.2s;
        }

        .btn-kembali:hover {
            background: #e2e8f0;
        }

        .btn-edit-user {
            padding: 10px 22px;
            background: #7ca5df;
            color: #ffffff;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            transition: background 0.2s;
        }

        .btn-edit-user:hover {
            background: #6a93cd;
        }
    </style>
</x-layout>