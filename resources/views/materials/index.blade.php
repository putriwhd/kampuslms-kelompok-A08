<x-layout>
    <x-slot:title>Materi - {{ $course->name }}</x-slot:title>

    <h1>Materi: {{ $course->name }}</h1>

    <div class="content-card">
        @forelse ($materials as $material)
            <article>
                <h2>
                    @can('view', $material)
                        <a href="{{ route($role . '.courses.materials.scoped-show', [
                            'course' => $course,
                            'material' => $material,
                            'as' => $role,
                        ]) }}">
                            {{ $material->title }}
                        </a>
                    @else
                        {{ $material->title }}
                    @endcan
                </h2>

                @if ($material->description)
                    <p>{{ $material->description }}</p>
                @endif

                @can('update', $material)
                    <p>
                        <strong>Jenis materi:</strong>
                        {{ ucfirst($material->type) }}
                    </p>
                @endcan
            </article>

            <hr>
        @empty
            <p>Belum ada materi untuk mata kuliah ini.</p>
        @endforelse
    </div>

    {{ $materials->links() }}

    <p>
        <a href="{{ route($role . '.courses.show', [
            'course' => $course,
            'as' => $role,
        ]) }}">
            Kembali ke Mata Kuliah
        </a>
    </p>
</x-layout>