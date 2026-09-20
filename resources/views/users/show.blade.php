<x-layout>
    <x-slot:title>Detail Pengguna</x-slot:title>

    <div class="user-detail-header">
        <div>
            <h1>Detail Pengguna</h1>
            <p>Informasi lengkap mengenai pengguna KampusLMS.</p>
        </div>
    </div>

    <div class="user-detail-card">

        <div class="user-detail-card-header">
            <div>
                <h2>{{ $user->name }}</h2>
                <p>Informasi akun pengguna</p>
            </div>

            <span class="user-role">
                {{ ucfirst($user->role) }}
            </span>
        </div>

        <table class="user-detail-table">
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
                    <td>
                        <span class="user-role">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                </tr>

                <tr>
                    <th>Terdaftar</th>
                    <td>
                        {{ $user->created_at?->format('d M Y H:i') ?? '-' }}
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="user-detail-footer">
            <a href="{{ route('users.index', ['as' => request('as', 'admin')]) }}"
               class="back-button">
                &larr; Kembali
            </a>

            <a href="{{ route('users.edit', [$user, 'as' => request('as', 'admin')]) }}"
               class="edit-button">
                Edit Pengguna
            </a>
        </div>

    </div>


    <style>
        .user-detail-header {
            margin-bottom: 28px;
        }

        .user-detail-header h1 {
            margin: 0 0 8px;
            color: var(--blue-dark);
            font-size: 27px;
            font-weight: 700;
        }

        .user-detail-header p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .user-detail-card {
            overflow: hidden;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .user-detail-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
        }

        .user-detail-card-header h2 {
            margin: 0 0 5px;
            color: var(--blue-dark);
            font-size: 19px;
            font-weight: 700;
        }

        .user-detail-card-header p {
            margin: 0;
            color: var(--muted);
            font-size: 13px;
        }

        .user-role {
            display: inline-flex;
            align-items: center;
            padding: 6px 11px;
            background: var(--blue-soft);
            color: var(--blue-dark);
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
            white-space: nowrap;
        }

        .user-detail-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .user-detail-table th {
            width: 220px;
            padding: 16px 24px;
            background: #f8fafc;
            color: var(--blue-dark);
            font-size: 13px;
            font-weight: 600;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }

        .user-detail-table td {
            padding: 16px 24px;
            color: var(--text);
            border-bottom: 1px solid var(--border);
        }

        .user-detail-table tr:last-child th,
        .user-detail-table tr:last-child td {
            border-bottom: none;
        }

        .user-detail-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            padding: 18px 24px;
            background: #f8fafc;
            border-top: 1px solid var(--border);
        }

        .back-button,
        .edit-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 9px 14px;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
        }

        .back-button {
            background: #f3f4f6;
            color: #555;
        }

        .edit-button {
            background: var(--blue);
            color: var(--white);
        }

        .back-button:hover {
            background: #e5e7eb;
        }

        .edit-button:hover {
            background: var(--blue-dark);
        }

        @media (max-width: 700px) {
            .user-detail-card-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .user-detail-table th {
                width: 130px;
                padding: 13px 14px;
            }

            .user-detail-table td {
                padding: 13px 14px;
            }

            .user-detail-footer {
                flex-direction: column;
                align-items: stretch;
            }

            .back-button,
            .edit-button {
                width: 100%;
            }
        }
    </style>
</x-layout>