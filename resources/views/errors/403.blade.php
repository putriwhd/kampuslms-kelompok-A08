<x-layout>
    <x-slot:title>
        Akses Ditolak
    </x-slot:title>

    <section class="error-page" aria-labelledby="forbidden-title">
        <p class="error-page__code">403</p>
        <h1 id="forbidden-title">Anda tidak memiliki akses</h1>
        <p>
            Akun Anda tidak memiliki izin untuk membuka halaman atau data ini.
            Jika Anda merasa seharusnya memiliki akses, hubungi administrator
            KampusLMS.
        </p>
        <a href="{{ route('dashboard') }}" class="primary-btn">
            Kembali ke Dashboard
        </a>
    </section>
</x-layout>
