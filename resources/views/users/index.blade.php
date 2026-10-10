<x-layout>
    <x-slot:title>Data Pengguna</x-slot:title>

    <div class="user-page-header">
        <div>
            <h1>Data Pengguna</h1>
            <p>Kelola data pengguna yang terdaftar di KampusLMS.</p>
        </div>

        @if ($role === 'admin')
            <a
                href="{{ route('admin.users.create', ['as' => $role]) }}"
                class="user-add-button"
            >
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
                <p>
                    Informasi pengguna berdasarkan nama, email, NIM/NIP, dan role.
                </p>
            </div>
        </div>

        {{-- FORM PENCARIAN & FILTER --}}
        <div class="user-filter-wrapper">
            <form
                action="{{ route('admin.users.index') }}"
                method="GET"
                class="user-filter-form"
            >
                <input
                    type="hidden"
                    name="as"
                    value="{{ $role }}"
                >

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
                        <option
                            value="admin"
                            @selected(request('role') === 'admin')
                        >
                            Admin
                        </option>
                        <option
                            value="dosen"
                            @selected(request('role') === 'dosen')
                        >
                            Dosen
                        </option>
                        <option
                            value="mahasiswa"
                            @selected(request('role') === 'mahasiswa')
                        >
                            Mahasiswa
                        </option>
                    </select>

                    <button type="submit" class="btn-search">
                        Cari
                    </button>

                    @if (request('search') || request('role'))
                        <a
                            href="{{ route('admin.users.index', ['as' => $role]) }}"
                            class="btn-reset"
                        >
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
                                <a
                                    href="{{ route('admin.users.show', [$user->id, 'as' => $role]) }}"
                                    class="action-view"
                                >
                                    Lihat
                                </a>

                                @if ($role === 'admin')
                                    <a
                                        href="{{ route('admin.users.edit', [$user->id, 'as' => $role]) }}"
                                        class="action-edit"
                                    >
                                        Edit
                                    </a>

                                    <form
                                        action="{{ route('admin.users.destroy', [$user->id, 'as' => $role]) }}"
                                        method="POST"
                                        class="delete-user-form"
                                        onsubmit="return confirm('Yakin ingin menghapus pengguna ini?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="action-delete"
                                        >
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
</x-layout>