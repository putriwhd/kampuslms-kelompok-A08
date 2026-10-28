<x-layout>
    <x-slot:title>Edit Tugas - {{ $assignment->title }}</x-slot:title>

    <div class="page-header">
        <h1 class="page-title">Edit Tugas</h1>
        <p class="page-lead">
            Perbarui informasi tugas untuk mata kuliah
            <strong>{{ $assignment->course->name }}</strong>.
        </p>
    </div>

    <section class="content-card assignment-form-card">
        <div class="assignment-form-header">
            <h2>Informasi Tugas</h2>
            <p>
                Sesuaikan informasi tugas di bawah ini, kemudian simpan perubahan.
            </p>
        </div>

        <form
            action="{{ route($role . '.assignments.update', [
                'assignment' => $assignment,
                'as' => $role,
            ]) }}"
            method="POST"
            class="assignment-form"
        >
            @csrf
            @method('PUT')

            @include('assignments._form')
        </form>
    </section>
</x-layout>