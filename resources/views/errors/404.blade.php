{{--
    Laravel otomatis memakai berkas ini (resources/views/errors/404.blade.php)
    setiap kali terjadi NotFoundHttpException/404 -- TIDAK perlu
    didaftarkan di routes/web.php sama sekali, ini konvensi bawaan
    framework. Berkas ini yang tampil kalau, misalnya,
    CourseController@show melempar NotFoundHttpException karena id
    tidak ditemukan di array data().

    Dibungkus <x-layout> juga supaya navbar & footer tetap konsisten
    dengan halaman lain, bukan halaman polos terpisah.
--}}
<x-layout>
    <x-slot:title>
        Halaman Tidak Ditemukan
    </x-slot:title>

    <h1>404 — Halaman Tidak Ditemukan</h1>
    <p>Maaf, halaman atau data yang Anda cari tidak tersedia.</p>

    <a href="{{ route('dashboard') }}">Kembali ke Dashboard</a>
</x-layout>