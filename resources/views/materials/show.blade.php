<x-layout>
    <x-slot:title>{{ $material->title }}</x-slot:title>

    <div class="content-card">
        <h1>{{ $material->title }}</h1>
        <p>{{ $material->description }}</p>
        <p>Mata Kuliah: {{ $material->course->name }}</p>

        @if ($material->type === 'link' && $material->external_url)
            <p>
                <a href="{{ $material->external_url }}" target="_blank" rel="noopener noreferrer">
                    Buka Materi
                </a>
            </p>
        @elseif ($material->original_name)
            <p>File: {{ $material->original_name }}</p>
        @endif

        <a href="{{ route($role . '.courses.materials.index', [$material->course, 'as' => $role]) }}">
            Kembali ke Daftar Materi
        </a>
    </div>
</x-layout>
