<x-layout>
    <x-slot:title>Data Pengguna</x-slot:title>

    <div class="page-header user-page-header">
        <div>
            <h1 class="page-title">Data Pengguna</h1>
            <p class="page-lead">
                Kelola data pengguna yang terdaftar di KampusLMS.
            </p>
        </div>

        @if ($role === 'admin')
            <a
                href="{{ route('admin.users.create', ['as' => $role]) }}"
                class="primary-btn user-add-button"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    aria-hidden="true">
                    <path d="M12 5v14"/>
                    <path d="M5 12h14"/>
                </svg>
                Tambah Pengguna
            </a>
        @endif
    </div>

    @if (session('success'))
        <div class="user-alert user-alert-success" role="status">
            <span class="user-alert-icon" aria-hidden="true">✓</span>
            <p>{{ session('success') }}</p>
        </div>
    @endif

    @if (session('error'))
        <div class="user-alert user-alert-error" role="alert">
            <span class="user-alert-icon" aria-hidden="true">!</span>
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <section class="content-card user-table-card">
        <header class="user-table-header">
            <div class="user-table-heading-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="10" cy="7" r="4"/>
                    <path d="M20 8v6"/>
                    <path d="M23 11h-6"/>
                </svg>
            </div>

            <div class="user-table-heading-content">
                <h2>Daftar Pengguna</h2>
                <p>
                    Informasi pengguna berdasarkan nama, email, NIM/NIP, dan peran.
                </p>
            </div>
        </header>

        <div class="user-filter-wrapper">
            <form
                action="{{ route('admin.users.index') }}"
                method="GET"
                class="user-filter-form"
            >
                <input type="hidden" name="as" value="{{ $role }}">

                <div class="user-filter-search">
                    <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        aria-hidden="true">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.35-4.35"/>
                    </svg>

                    <input
                        type="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari nama, email, atau NIM/NIP..."
                        class="user-filter-input"
                        aria-label="Cari pengguna"
                    >
                </div>

                <select
                    name="role"
                    class="user-filter-select"
                    aria-label="Filter berdasarkan peran"
                >
                    <option value="">Semua Peran</option>
                    <option value="admin" @selected(request('role') === 'admin')>
                        Admin
                    </option>
                    <option value="dosen" @selected(request('role') === 'dosen')>
                        Dosen
                    </option>
                    <option value="mahasiswa" @selected(request('role') === 'mahasiswa')>
                        Mahasiswa
                    </option>
                </select>

                <button type="submit" class="primary-btn user-search-button">
                    Cari
                </button>

                @if (request('search') || request('role'))
                    <a
                        href="{{ route('admin.users.index', ['as' => $role]) }}"
                        class="secondary-btn user-reset-button"
                    >
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <div class="user-table-meta">
            <span>Data pengguna</span>
            <span class="user-table-role-label">
                {{ ucfirst($role) }}
            </span>
        </div>

        <div class="user-table-wrapper">
            <table class="user-table">
                <thead>
                    <tr>
                        <th scope="col">Pengguna</th>
                        <th scope="col">Email</th>
                        <th scope="col">NIM/NIP</th>
                        <th scope="col">Peran</th>
                        <th scope="col" class="user-action-heading">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                <div class="user-identity">
                                    <div class="user-avatar" aria-hidden="true">
                                        {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                                    </div>

                                    <span class="user-name">
                                        {{ $user->name }}
                                    </span>
                                </div>
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
                                <span class="user-role-badge user-role-{{ $user->role }}">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>

                            <td>
                                <div class="user-actions">
                                    <a
                                        href="{{ route('admin.users.show', [$user->id, 'as' => $role]) }}"
                                        class="user-action-btn user-action-view"
                                    >
                                        Lihat
                                    </a>

                                    @if ($role === 'admin')
                                        <a
                                            href="{{ route('admin.users.edit', [$user->id, 'as' => $role]) }}"
                                            class="user-action-btn user-action-edit"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            action="{{ route('admin.users.destroy', [$user->id, 'as' => $role]) }}"
                                            method="POST"
                                            onsubmit="return confirm('Yakin ingin menghapus pengguna ini?')"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="user-action-btn user-action-delete"
                                            >
                                                Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="user-empty">
                                    <div class="user-empty-icon" aria-hidden="true">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="11" cy="11" r="8"/>
                                            <path d="m21 21-4.35-4.35"/>
                                        </svg>
                                    </div>

                                    <h3>Data pengguna tidak ditemukan</h3>
                                    <p>
                                        Belum ada pengguna yang sesuai dengan pencarian atau filter.
                                    </p>

                                    @if (request('search') || request('role'))
                                        <a
                                            href="{{ route('admin.users.index', ['as' => $role]) }}"
                                            class="secondary-btn user-empty-reset"
                                        >
                                            Tampilkan Semua Pengguna
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="user-pagination">
                {{ $users->appends(request()->query())->links('vendor.pagination.kampuslms') }}
            </div>
        @endif
    </section>
</x-layout>