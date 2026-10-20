<x-layout>
    <x-slot:title>Tugas - {{ $course->name }}</x-slot:title>

    <h1>Tugas: {{ $course->name }}</h1>

    @can('create', [\App\Models\Assignment::class, $course])
        <p>
            <a href="{{ route($role . '.courses.assignments.create', [
                'course' => $course,
                'as' => $role,
            ]) }}">Tambah Tugas</a>
        </p>
    @endcan

    <div class="content-card">
        @forelse ($assignments as $assignment)
            <article>
                <h2>
                    @can('view', $assignment)
                        <a href="{{ route($role . '.courses.assignments.scoped-show', [
                            'course' => $course,
                            'assignment' => $assignment,
                            'as' => $role,
                        ]) }}">
                            {{ $assignment->title }}
                        </a>
                    @else
                        {{ $assignment->title }}
                    @endcan
                </h2>

                <p>
                    Batas pengumpulan:
                    {{ $assignment->due_at?->format('d M Y H:i') ?? '-' }}
                </p>

                @can('update', $assignment)
                    <p>
                        Status: {{ ucfirst($assignment->status) }}
                    </p>
                    <a href="{{ route($role . '.assignments.edit', [
                        'assignment' => $assignment,
                        'as' => $role,
                    ]) }}">Edit</a>
                @endcan

                @can('delete', $assignment)
                    <form
                        action="{{ route($role . '.assignments.destroy', [
                            'assignment' => $assignment,
                            'as' => $role,
                        ]) }}"
                        method="POST"
                        class="delete-form"
                        onsubmit="return confirm('Yakin ingin menghapus tugas ini?')"
                    >
                        @csrf
                        @method('DELETE')
                        <button type="submit">Hapus</button>
                    </form>
                @endcan
            </article>

            <hr>
        @empty
            <p>Belum ada tugas untuk mata kuliah ini.</p>
        @endforelse
    </div>

    {{ $assignments->links('vendor.pagination.kampuslms') }}

    <p>
        <a href="{{ route($role . '.courses.show', [
            'course' => $course,
            'as' => $role,
        ]) }}">
            Kembali ke Mata Kuliah
        </a>
    </p>
</x-layout>