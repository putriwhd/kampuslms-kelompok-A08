<x-layout>
    <x-slot:title>Materi — {{ $course->name }}</x-slot:title>

    <h1>Materi: {{ $course->name }}</h1>

    <div class="content-card">
        @forelse ($materials as $material)
            <article>
                <h2>
                    <a href="{{ route($role . '.courses.materials.scoped-show', [$course, $material, 'as' => $role]) }}">
                        {{ $material->title }}
                    </a>
                </h2>
                <p>{{ $material->description }}</p>
            </article>
        @empty
            <p>Belum ada materi untuk mata kuliah ini.</p>
        @endforelse
    </div>

    {{ $materials->links() }}

    <p>
        <a href="{{ route($role . '.courses.show', [$course, 'as' => $role]) }}">
            Kembali ke Mata Kuliah
        </a>
    </p>
</x-layout>
