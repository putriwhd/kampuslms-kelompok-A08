<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $title ?? 'KampusLMS' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --blue-dark: #4f6f9f;
            --blue: #7fa6d8;
            --blue-light: #dceafa;
            --blue-soft: #f3f7fc;

            --white: #ffffff;
            --cream: #fbfdff;

            --text: #34445a;
            --muted: #718096;

            --border: #d8e4f2;

            --shadow: 0 8px 25px rgba(79, 111, 159, 0.10);

            --radius: 14px;
            --transition: 0.25s ease;
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            min-height: 100vh;

            background: var(--cream);
            color: var(--text);

            font-family:
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                Arial,
                sans-serif;

            line-height: 1.6;

            display: flex;
            flex-direction: column;
        }

        a {
            color: inherit;
        }

        /* =========================
           HEADER
        ========================= */

        .site-header {
            position: sticky;
            top: 0;
            z-index: 1000;

            background: rgba(255, 255, 255, 0.97);

            backdrop-filter: blur(10px);

            border-bottom: 1px solid var(--border);

            box-shadow:
                0 3px 15px rgba(79, 111, 159, 0.07);
        }

        .nav-container {
            width: 100%;
            max-width: 1100px;

            min-height: 70px;

            margin: 0 auto;
            padding: 0 24px;

            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* =========================
           LOGO
        ========================= */

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;

            color: var(--blue-dark);

            text-decoration: none;

            font-family: Georgia, "Times New Roman", serif;

            font-size: 21px;
            font-weight: 700;
        }

        .brand-icon {
            width: 38px;
            height: 38px;

            display: flex;
            align-items: center;
            justify-content: center;

            border-radius: 11px;

            background: var(--blue-light);
            color: var(--blue-dark);

            font-size: 18px;
            font-weight: 800;

            transition: transform var(--transition);
        }

        .brand:hover .brand-icon {
            transform: rotate(-5deg) scale(1.05);
        }

        /* =========================
           NAVIGATION & ROLE SELECTOR
        ========================= */

        .site-nav {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .site-nav a {
            position: relative;

            display: flex;
            align-items: center;
            gap: 7px;

            padding: 10px 15px;

            color: var(--muted);

            text-decoration: none;

            font-size: 14px;
            font-weight: 500;

            border-radius: 9px;

            transition:
                background var(--transition),
                color var(--transition),
                transform var(--transition);
        }

        .site-nav a:hover {
            color: var(--blue-dark);

            background: var(--blue-soft);

            transform: translateY(-1px);
        }

        .site-nav a.is-active {
            color: var(--blue-dark);

            background: var(--blue-light);

            font-weight: 600;
        }

        .site-nav a.is-active::after {
            content: "";

            position: absolute;

            left: 15px;
            right: 15px;
            bottom: 3px;

            height: 2px;

            background: var(--blue);

            border-radius: 10px;
        }

        /* =========================
           ROLE SELECTOR
        ========================= */

        .role-selector-form {
            display: inline-flex;
            align-items: center;
            margin-left: 10px;
        }

        .role-select {
            padding: 8px 12px;

            font-size: 13px;
            font-weight: 600;

            color: var(--blue-dark);

            background-color: var(--blue-soft);

            border: 1px solid var(--border);

            border-radius: 9px;

            outline: none;
            cursor: pointer;

            transition: all var(--transition);
        }

        .role-select:hover,
        .role-select:focus {
            background-color: var(--blue-light);
            border-color: var(--blue);
        }

        /* =========================
           MOBILE MENU
        ========================= */

        .menu-toggle {
            display: none;

            border: none;

            background: transparent;

            color: var(--blue-dark);

            cursor: pointer;

            font-size: 24px;

            padding: 6px;
        }

        /* =========================
           MAIN
        ========================= */

        .site-main {
            width: 100%;
            max-width: 1100px;

            min-height: calc(100vh - 140px);

            margin: 0 auto;

            padding: 45px 24px 70px;

            animation: pageFade 0.35s ease;
        }

        @keyframes pageFade {
            from {
                opacity: 0;
                transform: translateY(8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* =========================
           FOOTER
        ========================= */

        .site-footer {
            margin-top: auto;

            background: var(--blue-soft);

            border-top: 1px solid var(--border);
        }

        .footer-container {
            width: 100%;
            max-width: 1100px;

            margin: 0 auto;

            padding: 24px;

            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;
        }

        .footer-brand {
            color: var(--blue-dark);

            font-family:
                Georgia,
                "Times New Roman",
                serif;

            font-weight: 700;
        }

        .site-footer small {
            color: var(--muted);

            font-size: 12px;
        }

        /* =========================
           SCROLL TOP
        ========================= */

        .scroll-top {
            position: fixed;

            right: 22px;
            bottom: 22px;

            width: 42px;
            height: 42px;

            display: flex;

            align-items: center;
            justify-content: center;

            border: none;

            border-radius: 50%;

            background: var(--blue);

            color: white;

            cursor: pointer;

            font-size: 18px;

            font-weight: 700;

            opacity: 0;

            visibility: hidden;

            transform: translateY(10px);

            transition:
                opacity var(--transition),
                visibility var(--transition),
                transform var(--transition);

            box-shadow:
                0 6px 20px rgba(79, 111, 159, 0.20);
        }

        .scroll-top.show {
            opacity: 1;

            visibility: visible;

            transform: translateY(0);
        }

        .scroll-top:hover {
            background: var(--blue-dark);

            transform: translateY(-3px);
        }

        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 700px) {

            .nav-container {
                min-height: 64px;

                padding: 0 16px;
            }

            .menu-toggle {
                display: block;
            }

            .site-nav {
                position: absolute;

                top: 64px;

                left: 0;
                right: 0;

                display: none;

                flex-direction: column;

                align-items: stretch;

                padding: 12px 16px 16px;

                background: var(--white);

                border-top: 1px solid var(--border);

                box-shadow:
                    0 10px 25px rgba(79, 111, 159, 0.12);
            }

            .site-nav.open {
                display: flex;
            }

            .site-nav a {
                width: 100%;
            }

            .role-selector-form {
                margin-left: 0;
                margin-top: 8px;
                width: 100%;
            }

            .role-select {
                width: 100%;
            }

            .site-nav a.is-active::after {
                display: none;
            }

            .site-main {
                padding: 35px 16px 50px;
            }

            .footer-container {
                flex-direction: column;

                align-items: flex-start;
            }
        }
    </style>
</head>

<body>

    @php
        $availableRoles = ['mahasiswa', 'dosen', 'admin'];

        $selectedRole = in_array(request('as'), $availableRoles, true)
            ? request('as')
            : 'mahasiswa';
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
                    href="{{ route('courses.index', ['as' => $selectedRole]) }}"
                    class="{{ request()->routeIs('courses.*') ? 'is-active' : '' }}"
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
                        href="{{ route('users.index', ['as' => $selectedRole]) }}"
                        class="{{ request()->routeIs('users.*') ? 'is-active' : '' }}"
                    >
                        👥 Pengguna
                    </a>
                @endif

                {{-- DROPDOWN SIMULASI ROLE --}}
                <form
                    action="{{ route('dashboard') }}"
                    method="GET"
                    class="role-selector-form"
                >
                    <select
                        name="as"
                        class="role-select"
                        onchange="this.form.submit()"
                    >
                        <option
                            value="mahasiswa"
                            {{ $selectedRole === 'mahasiswa' ? 'selected' : '' }}
                        >
                            👤 Role: Mahasiswa
                        </option>

                        <option
                            value="dosen"
                            {{ $selectedRole === 'dosen' ? 'selected' : '' }}
                        >
                            👨‍🏫 Role: Dosen
                        </option>

                        <option
                            value="admin"
                            {{ $selectedRole === 'admin' ? 'selected' : '' }}
                        >
                            🛠️ Role: Admin
                        </option>
                    </select>
                </form>

            </nav>

        </div>

    </header>

    {{-- ISI HALAMAN --}}
    <main class="site-main">
        {{ $slot }}
    </main>

    {{-- FOOTER --}}
    <footer class="site-footer">

        <div class="footer-container">

            <div>
                <div class="footer-brand">KampusLMS</div>

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

    <script>

        // MOBILE MENU
        const menuToggle = document.getElementById('menuToggle');
        const siteNav = document.getElementById('siteNav');

        if (menuToggle && siteNav) {

            menuToggle.addEventListener('click', function () {

                const isOpen = siteNav.classList.toggle('open');

                menuToggle.setAttribute(
                    'aria-expanded',
                    isOpen ? 'true' : 'false'
                );

                menuToggle.innerHTML = isOpen ? '✕' : '☰';

            });

            siteNav.querySelectorAll('a').forEach(function (link) {

                link.addEventListener('click', function () {

                    siteNav.classList.remove('open');

                    menuToggle.setAttribute(
                        'aria-expanded',
                        'false'
                    );

                    menuToggle.innerHTML = '☰';

                });

            });

        }


        // SCROLL TO TOP
        const scrollTop = document.getElementById('scrollTop');

        if (scrollTop) {

            window.addEventListener('scroll', function () {

                if (window.scrollY > 300) {

                    scrollTop.classList.add('show');

                } else {

                    scrollTop.classList.remove('show');

                }

            });

            scrollTop.addEventListener('click', function () {

                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });

            });

        }

    </script>

</body>
</html>