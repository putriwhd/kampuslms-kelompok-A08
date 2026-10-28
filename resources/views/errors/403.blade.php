<x-layout>
    <x-slot:title>Akses Ditolak - KampusLMS</x-slot:title>

    <div class="error-page-wrapper">
        <section class="error-page" aria-labelledby="forbidden-title">
            <div class="error-page__icon" aria-hidden="true">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="48"
                    height="48"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.6"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 8v4"/>
                    <path d="M12 16h.01"/>
                </svg>
            </div>

            <p class="error-page__code">403</p>

            <h1 id="forbidden-title">Anda Tidak Memiliki Akses</h1>

            <p class="error-page__description">
                Akun Anda tidak memiliki izin untuk membuka halaman atau data ini.
                Jika Anda merasa seharusnya memiliki akses, silakan hubungi
                administrator KampusLMS.
            </p>

            <a href="{{ route('dashboard') }}" class="primary-btn error-page__button">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="18"
                    height="18"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path d="m3 10 9-7 9 7"/>
                    <path d="M5 9v12h14V9"/>
                    <path d="M9 21v-8h6v8"/>
                </svg>
                Kembali ke Dashboard
            </a>

            <p class="error-page__help">
                Kode kesalahan: <strong>403 Forbidden</strong>
            </p>
        </section>
    </div>
</x-layout>