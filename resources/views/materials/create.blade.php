<x-layout>
    <x-slot:title>Tambah Materi - {{ $course->name }}</x-slot:title>

    <section class="content-card">
        <h1>Tambah Materi</h1>
        <p>Mata kuliah: {{ $course->name }}</p>

        <form
            action="{{ route($role . '.courses.materials.store', [
                'course' => $course,
                'as' => $role,
            ]) }}"
            method="POST"
        >
            @csrf
            @include('materials._form')
        </form>
    </section>
</x-layout>
