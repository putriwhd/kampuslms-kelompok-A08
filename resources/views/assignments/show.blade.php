<x-layout>
    <x-slot:title>{{ $assignment->title }}</x-slot:title>

    <div class="content-card">
        <h1>{{ $assignment->title }}</h1>
        <p>Mata Kuliah: {{ $assignment->course->name }}</p>
        <p>{{ $assignment->instructions }}</p>
        <p>Batas pengumpulan: {{ $assignment->due_at?->format('d M Y H:i') ?? '-' }}</p>
        <p>Nilai maksimal: {{ $assignment->max_score }}</p>
        <p>Status: {{ ucfirst($assignment->status) }}</p>

        <a href="{{ route($role . '.courses.assignments.index', [$assignment->course, 'as' => $role]) }}">
            Kembali ke Daftar Tugas
        </a>
    </div>
</x-layout>
