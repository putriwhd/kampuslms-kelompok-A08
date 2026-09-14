{{-- 
    Dashboard masih murni statis (Minggu 1-2 belum ada login/role),
    jadi wajar dipanggil langsung lewat Route::view() tanpa controller.
--}}

<x-layout>

    <x-slot:title>
        Dashboard
    </x-slot:title>


    <div class="page-header">

        <h1 class="page-title">
            Dashboard KampusLMS
        </h1>

        <p class="page-lead">
            Selamat datang di KampusLMS.
            Silakan buka menu
            <a
                href="{{ route('courses.index') }}"
                class="interactive-link"
            >
                "Mata Kuliah"
            </a>
            untuk melihat daftar mata kuliah.
        </p>

    </div>


</x-layout>