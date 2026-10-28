@php
    $authUser = auth()->user();

    // Role: variabel dari halaman -> segmen URL -> ?as= -> akun login -> default
    $role = $role
        ?? (in_array(request()->segment(1), ['admin', 'dosen', 'mahasiswa'], true) ? request()->segment(1) : null)
        ?? request('as')
        ?? $authUser?->role
        ?? 'mahasiswa';

    $role = in_array($role, ['admin', 'dosen', 'mahasiswa'], true) ? $role : 'mahasiswa';
    $userName = $authUser?->name ?? 'Pengguna';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' - ' : '' }}KampusLMS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

<header class="site-header">
    <div class="nav-container">

        <a href="{{ route('dashboard') }}" class="brand">
            <span class="brand-icon">K</span>
            <span class="brand-name">KampusLMS</span>
        </a>

        <button type="button" class="menu-toggle" id="menuToggle"
                aria-label="Buka menu" aria-expanded="false" aria-controls="siteNav">
            <svg class="menu-toggle-icon" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                <path d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <nav class="site-nav" id="siteNav" aria-label="Navigasi utama">

            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'is-active' : '' }}">
                <span class="nav-icon" aria-hidden="true">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 10.5 12 3l9 7.5"/><path d="M5 9.5V21h14V9.5"/><path d="M10 21v-6h4v6"/></svg>
                </span>
                Dashboard
            </a>

            @auth
                <a href="{{ route($role . '.courses.index', ['as' => $role]) }}"
                   class="{{ request()->routeIs('*.courses.*') ? 'is-active' : '' }}">
                    <span class="nav-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                    </span>
                    Mata Kuliah
                </a>

                @if ($role === 'admin')
                    <a href="{{ route('admin.users.index') }}"
                       class="{{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}">
                        <span class="nav-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </span>
                        Pengguna
                    </a>
                @endif

                @if (in_array($role, ['dosen', 'mahasiswa'], true))
                    <a href="{{ route($role . '.grade-recap.page', ['as' => $role]) }}"
                       class="{{ request()->routeIs('*.grade-recap.*') ? 'is-active' : '' }}">
                        <span class="nav-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/></svg>
                        </span>
                        Rekap Nilai
                    </a>
                @endif
            @endauth

            @auth
                <div
                    class="notification-wrapper"
                    id="notificationWrapper"
                    data-endpoint="{{ route('notifications.preview') }}"
                >
                    <button
                        type="button"
                        class="notification-bell"
                        aria-label="Buka notifikasi"
                        aria-expanded="false"
                        aria-controls="notificationDropdown"
                        id="notificationBell"
                    >
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>
                        <span class="notification-badge" id="notificationBadge" hidden>0</span>
                    </button>
                    <section
                        class="notification-dropdown"
                        id="notificationDropdown"
                        aria-label="Notifikasi terbaru"
                        hidden
                    >
                        <header class="notification-dropdown-header">
                            <strong>Notifikasi terbaru</strong>
                            <a href="{{ route('notifications.index', ['as' => $role]) }}">Lihat semua</a>
                        </header>
                        <div id="notificationPreview" aria-live="polite">
                            <p class="notification-empty">Memuat notifikasi...</p>
                        </div>
                    </section>
                </div>

                <div class="nav-user">
                    <span class="nav-user-avatar" aria-hidden="true">{{ strtoupper(substr($userName, 0, 1)) }}</span>
                    <div class="nav-user-info" title="{{ $userName }}">
                        <span class="nav-user-name">{{ $userName }}</span>
                        <span class="nav-user-role">{{ ucfirst($role) }}</span>
                    </div>

                    <form method="POST" action="{{ route('logout') }}" class="nav-logout-form">
                        @csrf
                        <button type="submit" class="nav-logout">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
                            Keluar
                        </button>
                    </form>
                </div>
            @else
                <a href="{{ route('login') }}" class="{{ request()->routeIs('login') ? 'is-active' : '' }}">Masuk</a>
            @endauth
        </nav>
    </div>
</header>

<main class="site-main">
    @if (session('success'))
        <div class="global-alert global-alert-success" role="status">
            <span class="global-alert-icon" aria-hidden="true">✓</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if (session('error'))
        <div class="global-alert global-alert-error" role="alert">
            <span class="global-alert-icon" aria-hidden="true">!</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{ $slot }}
</main>

<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-brand-group">
            <a href="{{ route('dashboard') }}" class="footer-brand">KampusLMS</a>
            <small>Sistem manajemen pembelajaran kampus.</small>
        </div>
        <span class="footer-copyright">&copy; {{ date('Y') }} KampusLMS. Hak cipta dilindungi.</span>
    </div>
</footer>

<button type="button" class="scroll-top" id="scrollTop" aria-label="Kembali ke atas">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 15l-6-6-6 6"/></svg>
</button>

<script>
    (function () {
        // Menu mobile
        const btn = document.getElementById('menuToggle');
        const nav = document.getElementById('siteNav');
        if (btn && nav) {
            btn.addEventListener('click', function () {
                const open = nav.classList.toggle('is-open');
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            });
        }

        // Tombol kembali ke atas
        const top = document.getElementById('scrollTop');
        if (top) {
            window.addEventListener('scroll', function () {
                top.classList.toggle('show', window.scrollY > 300);
            }, { passive: true });
            top.addEventListener('click', function () {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }

        @auth
        // Badge jumlah notifikasi belum dibaca
        const badge = document.getElementById('notificationBadge');
        if (badge && window.fetch) {
            fetch(@json(route('notifications.unread-count')), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (data) {
                    if (!data) return;
                    const n = Number(data.count ?? data.unread_count ?? data.unread ?? data.data?.count ?? 0);
                    if (n > 0) {
                        badge.textContent = n > 99 ? '99+' : n;
                        badge.hidden = false;
                    }
                })
                .catch(function () {});
        }
        @endauth
    })();
</script>

</body>
</html>