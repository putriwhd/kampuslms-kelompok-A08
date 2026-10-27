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
                        <span style="display:inline-flex;align-items:center;gap:0.3rem">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <line x1="16" y1="13" x2="8" y2="13"/>
                                <line x1="16" y1="17" x2="8" y2="17"/>
                                <polyline points="10 9 9 9 8 9"/>
                            </svg>
                            Berkas Materi
                        </span>
                    @elseif ($material->type === 'link')
                        <span style="display:inline-flex;align-items:center;gap:0.3rem">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
                            </svg>
                            Tautan Eksternal
                        </span>
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
                            {{ $material->formatted_file_size }}
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