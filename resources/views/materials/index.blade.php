<x-layout>
    <x-slot:title>Materi - {{ $course->name }}</x-slot:title>

    <h1>Materi: {{ $course->name }}</h1>

    @if (session('success'))
        <p>{{ session('success') }}</p>
    @endif

    @can('create', [\App\Models\Material::class, $course])
        <p>
            <a href="{{ route($role . '.courses.materials.create', [
                'course' => $course,
                'as' => $role,
            ]) }}">
                Tambah Materi
            </a>
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

                <p>
                    <strong>Jenis Materi:</strong>
                    @if ($material->type === 'file')
                        <span>Berkas Materi</span>
                    @elseif ($material->type === 'link')
                        <span>Tautan Eksternal</span>
                    @else
                        <span>{{ ucfirst($material->type) }}</span>
                    @endif
                </p>

                @if ($material->type === 'file')
                    @if ($material->original_name)
                        <p>
                            <strong>Nama Berkas:</strong>
                            {{ $material->original_name }}
                        </p>
                    @endif

                    @if ($material->file_size)
                        <p>
                            <strong>Ukuran Berkas:</strong>
                            @if ($material->file_size >= 1048576)
                                {{ number_format($material->file_size / 1048576, 2) }} MB
                            @else
                                {{ number_format($material->file_size / 1024, 2) }} KB
                            @endif
                        </p>
                    @endif

                    @can('view', $material)
                        <p>
                            <a href="{{ route('materials.download', [
                                'material' => $material,
                            ]) }}">
                                Unduh Berkas Materi
                            </a>
                        </p>
                    @endcan

                @elseif ($material->type === 'link' && $material->external_url)
                    <p>Materi tersedia melalui tautan eksternal.</p>

                    <p>
                        <a
                            href="{{ $material->external_url }}"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            Buka Tautan Materi
                        </a>
                    </p>
                @endif

                @can('update', $material)
                    <p>
                        <a href="{{ route($role . '.materials.edit', [
                            'material' => $material,
                            'as' => $role,
                        ]) }}">
                            Edit Materi
                        </a>
                    </p>
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

    {{ $materials->links('vendor.pagination.kampuslms') }}

    <p>
        <a href="{{ route($role . '.courses.show', [
            'course' => $course,
            'as' => $role,
        ]) }}">
            Kembali ke Mata Kuliah
        </a>
    </p>
</x-layout>
