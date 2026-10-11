<x-layout>
    <x-slot:title>Detail Pengguna</x-slot:title>

    <div class="user-page-container">
        <p class="sub-heading-text">
            Informasi lengkap mengenai pengguna KampusLMS.
        </p>

        <div class="user-detail-card">
            <div class="card-top-header">
                <div>
                    <h1 class="user-name-title">
                        {{ $user->name }}
                    </h1>

                    <p class="user-sub-info">
                        Informasi akun pengguna
                    </p>
                </div>

                <span class="badge-role">
                    {{ ucfirst($user->role) }}
                </span>
            </div>

            <div class="card-detail-table">
                <div class="table-row">
                    <div class="table-label">Nama</div>

                    <div class="table-value">
                        {{ $user->name }}
                    </div>
                </div>

                <div class="table-row">
                    <div class="table-label">Email</div>

                    <div class="table-value">
                        {{ $user->email }}
                    </div>
                </div>

                <div class="table-row">
                    <div class="table-label">NIM/NIP</div>

                    <div class="table-value">
                        {{ $user->nim_nip ?? $user->identity_number ?? '-' }}
                    </div>
                </div>

                <div class="table-row">
                    <div class="table-label">Role</div>

                    <div class="table-value">
                        <span class="badge-role">
                            {{ ucfirst($user->role) }}
                        </span>
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
                <a
                    href="{{ route('admin.users.index', ['as' => $role]) }}"
                    class="btn-kembali"
                >
                    &larr; Kembali
                </a>

                <a
                    href="{{ route('admin.users.edit', [$user->id, 'as' => $role]) }}"
                    class="btn-edit-user"
                >
                    Edit Pengguna
                </a>
            </div>
        </div>
    </div>
</x-layout>