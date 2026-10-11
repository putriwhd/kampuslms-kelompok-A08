
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
        $selectedRole = auth()->user()?->role ?? 'mahasiswa';
    @endphp

    {{-- HEADER --}}
    <header class="site-header">

        <div class="nav-container">

            {{-- LOGO --}}
            <a href="{{ route('dashboard', ['as' => $selectedRole]) }}" class="brand">
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
            >
                ☰
            </button>

            {{-- NAVIGATION --}}
            <nav class="site-nav" id="siteNav">

                <a
                    href="{{ route('dashboard', ['as' => $selectedRole]) }}"
                    class="{{ request()->routeIs('dashboard') ? 'is-active' : '' }}"
                >
                    🏠 Dashboard
                </a>

                <a
                    href="{{ route($selectedRole . '.courses.index', ['as' => $selectedRole]) }}"
                    class="{{ request()->routeIs('*.courses.*') ? 'is-active' : '' }}"
                >
                    📚 Mata Kuliah
                </a>

                <a
                    href="{{ route('tentang', ['as' => $selectedRole]) }}"
                    class="{{ request()->routeIs('tentang') ? 'is-active' : '' }}"
                >
                    ℹ️ Tentang
                </a>

                @if ($selectedRole === 'admin')
                    <a
                        href="{{ route('admin.users.index', ['as' => $selectedRole]) }}"
                        class="{{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}"
                    >
                        👥 Pengguna
                    </a>
                @endif

                @guest
                    <a href="{{ route('login') }}">Masuk</a>
                @else
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit">Keluar</button>
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
    <button
        type="button"
        class="scroll-top"
        id="scrollTop"
        aria-label="Kembali ke atas"
    >
        ↑
    </button>

</body>
</html>
