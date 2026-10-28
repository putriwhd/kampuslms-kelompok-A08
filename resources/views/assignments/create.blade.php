 <x-layout>
    <x-slot:title>Tambah Tugas - {{ $course->name }}</x-slot:title>

    <div class="page-header">
        <h1 class="page-title">Tambah Tugas</h1>
        <p class="page-lead">
            Lengkapi informasi tugas untuk mata kuliah
            <strong>{{ $course->name }}</strong>.
        </p>
    </div>

    <section class="content-card assignment-form-card">
        <div class="assignment-form-header">
            <h2>Informasi Tugas</h2>
            <p>Isi semua informasi berikut dengan benar.</p>
        </div>

        <form
            action="{{ route($role . '.courses.assignments.store', [
                'course' => $course,
                'as' => $role,
            ]) }}"
            method="POST"
            class="assignment-form"
        >
            @csrf

            <input
                type="hidden"
                name="course_id"
                value="{{ $course->id }}"
            >

            @include('assignments._form')
        </form>
    </section>
</x-layout>
