<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'KampusLMS' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body>

    @php
        $currentUser = auth()->user();
        $selectedRole = $currentUser?->role;
    @endphp

    {{-- HEADER --}}
    <header class="site-header">

        <div class="nav-container">

            {{-- LOGO --}}
            <a href="{{ route('dashboard') }}" class="brand">
                <span class="brand-icon">K</span>
                <span>KampusLMS</span>
            </a>

            {{-- MOBILE BUTTON --}}
            <button
                type="button"
                class="menu-toggle"
                id="menuToggle"
                aria-label="Buka menu"
                aria-expanded="false"
                aria-controls="siteNav"
            >
                ☰
            </button>

            {{-- NAVIGATION --}}
            <nav class="site-nav" id="siteNav">

                <a
                    href="{{ route('dashboard') }}"
                    @class(['is-active' => request()->routeIs('dashboard')])
                    @if (request()->routeIs('dashboard')) aria-current="page" @endif
                >
                    🏠 Dashboard
                </a>

                @if ($currentUser)
                    <a
                        href="{{ route($selectedRole . '.courses.index') }}"
                        @class(['is-active' => request()->routeIs('*.courses.*')])
                        @if (request()->routeIs('*.courses.*')) aria-current="page" @endif
                    >
                        📚 Mata Kuliah
                    </a>

                    @if ($selectedRole === 'admin')
                        <a
                            href="{{ route('admin.users.index') }}"
                            @class(['is-active' => request()->routeIs('admin.users.*')])
                            @if (request()->routeIs('admin.users.*')) aria-current="page" @endif
                        >
                            👥 Pengguna
                        </a>
                    @endif

                    <a
                        href="{{ route('tentang') }}"
                        @class(['is-active' => request()->routeIs('tentang')])
                        @if (request()->routeIs('tentang')) aria-current="page" @endif
                    >
                        ℹ️ Tentang
                    </a>
                @endif

                @guest
                    <a
                        href="{{ route('login') }}"
                        @class(['is-active' => request()->routeIs('login')])
                        @if (request()->routeIs('login')) aria-current="page" @endif
                    >
                        Masuk
                    </a>
                @else
                    <span class="nav-user">
                        {{ $currentUser->name }}
                        <span class="nav-user-role">{{ ucfirst($selectedRole) }}</span>
                    </span>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="nav-logout">Keluar</button>
                    </form>
                @endguest

            </nav>

        </div>

    </header>

    {{-- ISI HALAMAN --}}
    <main class="site-main">

        @if (session('success'))
            <div class="global-alert global-alert-success" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="global-alert global-alert-error" role="alert">
                {{ session('error') }}
            </div>
        @endif

        {{ $slot }}

    </main>

    {{-- FOOTER --}}
    <footer class="site-footer">

        <div class="footer-container">

            <div>
                <div class="footer-brand">
                    KampusLMS
                </div>

                <small>
                    Sistem Informasi Pembelajaran Kampus
                </small>
            </div>

            <small>
                &copy; {{ date('Y') }} KampusLMS
            </small>

        </div>

    </footer>

    {{-- SCROLL TO TOP --}}
</body>
</html>