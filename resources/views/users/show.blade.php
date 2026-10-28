<x-layout>
    <x-slot:title>Detail Pengguna</x-slot:title>

    <div class="page-header user-detail-page-header">
        <div>
            <h1 class="page-title">Detail Pengguna</h1>
            <p class="page-lead">
                Informasi lengkap mengenai pengguna yang terdaftar di KampusLMS.
            </p>
        </div>
    </div>

    <section class="content-card user-detail-card">
        <header class="user-detail-header">
            <div class="user-detail-identity">
                <div class="user-detail-avatar" aria-hidden="true">
                    {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                </div>

                <div class="user-detail-heading">
                    <h2>{{ $user->name }}</h2>
                    <p>Informasi akun pengguna</p>
                </div>
            </div>

            <span class="user-detail-role user-role-{{ $user->role }}">
                {{ ucfirst($user->role) }}
            </span>
        </header>

        <div class="user-detail-section-heading">
            <div>
                <h3>Informasi Pribadi</h3>
                <p>Rincian identitas dan akun pengguna.</p>
            </div>
        </div>

        <dl class="user-detail-list">
            <div class="user-detail-row">
                <dt class="user-detail-label">
                    <span class="user-detail-field-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="8" r="4"/>
                            <path d="M5 21v-2a7 7 0 0 1 14 0v2"/>
                        </svg>
                    </span>
                    Nama Lengkap
                </dt>
                <dd class="user-detail-value">{{ $user->name }}</dd>
            </div>

            <div class="user-detail-row">
                <dt class="user-detail-label">
                    <span class="user-detail-field-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="5" width="18" height="14" rx="2"/>
                            <path d="m3 7 9 6 9-6"/>
                        </svg>
                    </span>
                    Alamat Email
                </dt>
                <dd class="user-detail-value">
                    <a href="mailto:{{ $user->email }}" class="user-detail-email">
                        {{ $user->email }}
                    </a>
                </dd>
            </div>

            <div class="user-detail-row">
                <dt class="user-detail-label">
                    <span class="user-detail-field-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="16" rx="2"/>
                            <path d="M7 8h10M7 12h10M7 16h5"/>
                        </svg>
                    </span>
                    NIM/NIP
                </dt>
                <dd class="user-detail-value">
                    {{ $user->nim_nip ?? $user->identity_number ?? '-' }}
                </dd>
            </div>

            <div class="user-detail-row">
                <dt class="user-detail-label">
                    <span class="user-detail-field-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3 4 7v5c0 5 3.5 8 8 9 4.5-1 8-4 8-9V7Z"/>
                            <path d="m9 12 2 2 4-4"/>
                        </svg>
                    </span>
                    Peran Pengguna
                </dt>
                <dd class="user-detail-value">
                    <span class="user-detail-role user-role-{{ $user->role }}">
                        {{ ucfirst($user->role) }}
                    </span>
                </dd>
            </div>

            <div class="user-detail-row">
                <dt class="user-detail-label">
                    <span class="user-detail-field-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M12 7v5l3 2"/>
                        </svg>
                    </span>
                    Tanggal Terdaftar
                </dt>
                <dd class="user-detail-value">
                    {{ $user->created_at?->format('d M Y, H:i') ?? '-' }}
                </dd>
            </div>
        </dl>

        <footer class="user-detail-actions">
            <a
                href="{{ route('admin.users.index', ['as' => $role]) }}"
                class="secondary-btn user-detail-back"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    aria-hidden="true">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
                Kembali ke Daftar
            </a>

            @if ($role === 'admin')
                <a
                    href="{{ route('admin.users.edit', [$user->id, 'as' => $role]) }}"
                    class="primary-btn user-detail-edit"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        aria-hidden="true">
                        <path d="M12 20h9"/>
                        <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L9 17l-4 1 1-4Z"/>
                    </svg>
                    Edit Pengguna
                </a>
            @endif
        </footer>
    </section>
</x-layout>