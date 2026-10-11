<x-layout>
    <x-slot:title>Edit Materi - {{ $material->title }}</x-slot:title>

    <section class="content-card">
        <h1>Edit Materi</h1>
        <p>Mata kuliah: {{ $material->course->name }}</p>

        <form
            action="{{ route($role . '.materials.update', [
                'material' => $material,
                'as' => $role,
            ]) }}"
            method="POST"
        >
            @csrf
            @method('PUT')
            @include('materials._form')
        </form>
    </section>
</x-layout>
