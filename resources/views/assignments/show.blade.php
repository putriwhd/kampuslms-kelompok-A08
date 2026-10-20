<x-layout>
    <x-slot:title>{{ $assignment->title }}</x-slot:title>

    <article class="content-card">
        <h1>{{ $assignment->title }}</h1>
        <p>Mata kuliah: {{ $assignment->course->name }}</p>
        <p>
            Batas pengumpulan:
            {{ $assignment->due_at?->format('d M Y H:i') ?? '-' }}
        </p>
        <p>Nilai maksimal: {{ $assignment->max_score }}</p>
        <div>
            <h2>Instruksi</h2>
            <p>{{ $assignment->instructions }}</p>
        </div>

        @can('update', $assignment)
            <p>Status: {{ ucfirst($assignment->status) }}</p>
            <a href="{{ route($role . '.assignments.edit', [
                'assignment' => $assignment,
                'as' => $role,
            ]) }}">Edit Tugas</a>
        @endcan

        @can('delete', $assignment)
            <form
                action="{{ route($role . '.assignments.destroy', [
                    'assignment' => $assignment,
                    'as' => $role,
                ]) }}"
                method="POST"
                onsubmit="return confirm('Yakin ingin menghapus tugas ini?')"
            >
                @csrf
                @method('DELETE')
                <button type="submit">Hapus Tugas</button>
            </form>
        @endcan

        <p>
            <a href="{{ route($role . '.courses.assignments.index', [
                'course' => $assignment->course,
                'as' => $role,
            ]) }}">Kembali ke Daftar Tugas</a>
        </p>
    </article>
</x-layout>
