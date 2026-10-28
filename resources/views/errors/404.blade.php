 <x-layout>
    <x-slot:title>Halaman Tidak Ditemukan - KampusLMS</x-slot:title>

    <div class="error-page-wrapper">
        <section class="error-page" aria-labelledby="not-found-title">
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
                    <circle cx="11" cy="11" r="8"/>
                    <path d="m21 21-4.35-4.35"/>
                    <path d="M8.5 9a2.5 2.5 0 0 1 5 0c0 1.5-2.5 2-2.5 3.5"/>
                    <path d="M11 15.5h.01"/>
                </svg>
            </div>

            <p class="error-page__code">404</p>

            <h1 id="not-found-title">Halaman Tidak Ditemukan</h1>

            <p class="error-page__description">
                Maaf, halaman atau data yang Anda cari tidak tersedia.
                Alamat mungkin salah, atau data yang dimaksud sudah tidak ditemukan.
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
                Kode kesalahan: <strong>404 Not Found</strong>
            </p>
        </section>
    </div>
</x-layout>