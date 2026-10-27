<x-layout>
    <x-slot:title>{{ $material->title }}</x-slot:title>

    <div class="content-card">
        <h1>{{ $material->title }}</h1>

        @if (session('success'))
            <p>{{ session('success') }}</p>
        @endif

        @if ($material->description)
            <p>{{ $material->description }}</p>
        @endif

        <p>
            <strong>Mata Kuliah:</strong>
            {{ $material->course->name }}
        </p>

        <p>
            <strong>Jenis Materi:</strong>

            @if ($material->type === 'file')
                <span style="display:inline-flex;align-items:center;gap:0.3rem">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="16"
                        height="16"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
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
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="16"
                        height="16"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
                    </svg>
                    Tautan Eksternal
                </span>
            @else
                <span>{{ ucfirst($material->type) }}</span>
            @endif
        </p>

        <hr>

        @if ($material->type === 'link' && $material->external_url)
            <h2>Tautan Materi</h2>

            <p>Materi ini tersedia melalui tautan eksternal.</p>

            <p>
                <a
                    href="{{ $material->external_url }}"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    Buka Materi
                </a>
            </p>

        @elseif ($material->type === 'file')
            <h2>Berkas Materi</h2>

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

            @if (!$material->original_name)
                <p>Informasi berkas belum tersedia.</p>
            @endif

        @else
            <p>Informasi materi belum tersedia.</p>
        @endif

        <hr>

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

        <p>
            <a href="{{ route($role . '.courses.materials.index', [
                'course' => $material->course,
                'as' => $role,
            ]) }}">
                Kembali ke Daftar Materi
            </a>
        </p>
    </div>
</x-layout>