<x-layout>
    <x-slot:title>Tugas - {{ $course->name }}</x-slot:title>

    <h1>Tugas: {{ $course->name }}</h1>

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
                @endcan
            </article>

            <hr>
        @empty
            <p>Belum ada tugas untuk mata kuliah ini.</p>
        @endforelse
    </div>

    {{ $assignments->links() }}

    <p>
        <a href="{{ route($role . '.courses.show', [
            'course' => $course,
            'as' => $role,
        ]) }}">
            Kembali ke Mata Kuliah
        </a>
    </p>
</x-layout>