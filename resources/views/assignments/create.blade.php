<x-layout>
    <x-slot:title>Tambah Tugas - {{ $course->name }}</x-slot:title>

    <section class="content-card">
        <h1>Tambah Tugas</h1>
        <p>Mata kuliah: {{ $course->name }}</p>

        <form
            action="{{ route($role . '.courses.assignments.store', [
                'course' => $course,
                'as' => $role,
            ]) }}"
            method="POST"
        >
            @csrf
            <input type="hidden" name="course_id" value="{{ $course->id }}">
            @include('assignments._form')
        </form>
    </section>
</x-layout>
