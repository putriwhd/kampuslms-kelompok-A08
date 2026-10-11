<x-layout>
    <x-slot:title>Edit Tugas - {{ $assignment->title }}</x-slot:title>

    <section class="content-card">
        <h1>Edit Tugas</h1>
        <p>Mata kuliah: {{ $assignment->course->name }}</p>

        <form
            action="{{ route($role . '.assignments.update', [
                'assignment' => $assignment,
                'as' => $role,
            ]) }}"
            method="POST"
        >
            @csrf
            @method('PUT')
            @include('assignments._form')
        </form>
    </section>
</x-layout>
