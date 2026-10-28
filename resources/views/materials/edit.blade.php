<x-layout>
    <x-slot:title>Edit Materi - {{ $material->title }}</x-slot:title>

    <div class="page-header">
        <h1 class="page-title">Edit Materi</h1>
        <p class="page-lead">
            Perbarui informasi materi pembelajaran pada mata kuliah
            <strong>{{ $material->course->name }}</strong>.
        </p>
    </div>

    <section class="content-card material-create-card">
        <header class="material-create-header">
            <div class="material-create-header-icon" aria-hidden="true">
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="25"
                    height="25"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.8"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="M12 20h9"/>
                    <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                </svg>
            </div>

            <div>
                <h2>Informasi Materi</h2>
                <p>
                    Periksa dan sesuaikan informasi materi sebelum menyimpan perubahan.
                </p>
            </div>
        </header>

        <div class="material-edit-current-info">
            <span class="material-edit-info-label">Materi yang sedang diedit</span>
            <strong>{{ $material->title }}</strong>
            <span>{{ $material->course->name }}</span>
        </div>

        <form
            action="{{ route($role . '.materials.update', [
                'material' => $material,
                'as' => $role,
            ]) }}"
            method="POST"
            enctype="multipart/form-data"
            class="material-create-form"
        >
            @csrf
            @method('PUT')

            @include('materials._form')
        </form>
    </section>
</x-layout>