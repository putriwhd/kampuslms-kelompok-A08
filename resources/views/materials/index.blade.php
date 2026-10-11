<x-layout>
    <x-slot:title>Materi - {{ $course->name }}</x-slot:title>

    <h1>Materi: {{ $course->name }}</h1>

    @can('create', [\App\Models\Material::class, $course])
        <p>
            <a href="{{ route($role . '.courses.materials.create', [
                'course' => $course,
                'as' => $role,
            ]) }}">Tambah Materi</a>
        </p>
    @endcan

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

                @can('update', $material)
                    <a href="{{ route($role . '.materials.edit', [
                        'material' => $material,
                        'as' => $role,
                    ]) }}">Edit</a>
                @endcan

                @can('delete', $material)
                    <form
                        action="{{ route($role . '.materials.destroy', [
                            'material' => $material,
                            'as' => $role,
                        ]) }}"
                        method="POST"
                        onsubmit="return confirm('Yakin ingin menghapus materi ini?')"
                    >
                        @csrf
                        @method('DELETE')
                        <button type="submit">Hapus</button>
                    </form>
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