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
                <span>Berkas Materi</span>
            @elseif ($material->type === 'link')
                <span>Tautan Eksternal</span>
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
