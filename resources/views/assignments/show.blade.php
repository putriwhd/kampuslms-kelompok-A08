<x-layout>
    <x-slot:title>Tugas — {{ $course->name }}</x-slot:title>

    <h1>Tugas: {{ $course->name }}</h1>

    <div class="content-card">
        @forelse ($assignments as $assignment)
            <article>
                <h2>
                    <a href="{{ route($role . '.courses.assignments.scoped-show', [$course, $assignment, 'as' => $role]) }}">
                        {{ $assignment->title }}
                    </a>
                </h2>
                <p>Batas pengumpulan: {{ $assignment->due_at?->format('d M Y H:i') ?? '-' }}</p>
                <p>Status: {{ ucfirst($assignment->status) }}</p>
            </article>
        @empty
            <p>Belum ada tugas untuk mata kuliah ini.</p>
        @endforelse
    </div>

    {{ $assignments->links() }}

    <p>
        <a href="{{ route($role . '.courses.show', [$course, 'as' => $role]) }}">
            Kembali ke Mata Kuliah
        </a>
    </p>
</x-layout>
