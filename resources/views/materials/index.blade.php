<x-layout>
    <x-slot:title>Materi - {{ $course->name }}</x-slot:title>

    <div class="page-header material-list-header">
        <div>
            <h1 class="page-title">Materi Pembelajaran</h1>
            <p class="page-lead">
                Daftar materi untuk mata kuliah
                <strong>{{ $course->name }}</strong>.
            </p>
        </div>

        @can('create', [\App\Models\Material::class, $course])
            <a
                href="{{ route($role . '.courses.materials.create', [
                    'course' => $course,
                    'as' => $role,
                ]) }}"
                class="primary-btn material-add-btn"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    aria-hidden="true">
                    <path d="M12 5v14"/>
                    <path d="M5 12h14"/>
                </svg>
                Tambah Materi
            </a>
        @endcan
    </div>

    @if (session('success'))
        <div class="material-list-alert" role="status">
            <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19"
                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                aria-hidden="true">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <path d="m9 11 3 3L22 4"/>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <section class="content-card material-list-card">
        <header class="material-list-card-header">
            <div>
                <h2>Daftar Materi</h2>
                <p>Materi dapat berupa berkas pembelajaran atau tautan eksternal.</p>
            </div>

            <span class="material-list-count">
                {{ $materials->total() }} materi
            </span>
        </header>

        <div class="material-list-container">
            @forelse ($materials as $material)
                <article class="material-list-item">
                    <div class="material-list-item-icon" aria-hidden="true">
                        @if ($material->type === 'file')
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.7" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <path d="M14 2v6h6"/>
                                <path d="M8 13h8"/>
                                <path d="M8 17h8"/>
                            </svg>
                        @elseif ($material->type === 'link')
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.7" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
                            </svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.7" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>
                            </svg>
                        @endif
                    </div>

                    <div class="material-list-item-content">
                        <div class="material-list-item-heading">
                            <h3>
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
                            </h3>

                            <span class="material-type-badge {{ $material->type === 'file' ? 'is-file' : ($material->type === 'link' ? 'is-link' : '') }}">
                                {{ $material->type === 'file' ? 'Berkas' : ($material->type === 'link' ? 'Tautan' : ucfirst($material->type)) }}
                            </span>
                        </div>

                        @if ($material->description)
                            <p class="material-list-description">
                                {{ $material->description }}
                            </p>
                        @endif

                        @if ($material->type === 'file')
                            <div class="material-file-meta">
                                @if ($material->original_name)
                                    <span>
                                        <strong>Nama berkas:</strong>
                                        {{ $material->original_name }}
                                    </span>
                                @endif

                                @if ($material->file_size)
                                    <span>
                                        <strong>Ukuran:</strong>
                                        {{ $material->formatted_file_size ?? number_format($material->file_size / 1024 / 1024, 2) . ' MB' }}
                                    </span>
                                @endif
                            </div>
                        @elseif ($material->type === 'link' && $material->external_url)
                            <p class="material-list-description">
                                Materi tersedia melalui tautan eksternal.
                            </p>
                        @endif

                        <div class="material-list-actions">
                            @if ($material->type === 'file')
                                <a
                                    href="{{ route('materials.download', [
                                        'material' => $material,
                                    ]) }}"
                                    class="material-action-btn material-action-primary"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        aria-hidden="true">
                                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                        <path d="m7 10 5 5 5-5"/>
                                        <path d="M12 15V3"/>
                                    </svg>
                                    Unduh Berkas Materi
                                </a>
                            @elseif ($material->type === 'link' && $material->external_url)
                                <a
                                    href="{{ $material->external_url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="material-action-btn material-action-primary"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                        aria-hidden="true">
                                        <path d="M15 3h6v6"/>
                                        <path d="M10 14 21 3"/>
                                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                    </svg>
                                    Buka Tautan
                                </a>
                            @endif

                            @can('update', $material)
                                <a
                                    href="{{ route($role . '.materials.edit', [
                                        'material' => $material,
                                        'as' => $role,
                                    ]) }}"
                                    class="material-action-btn material-action-secondary"
                                >
                                    Edit Materi
                                </a>
                            @endcan

                            @can('delete', $material)
                                <form
                                    action="{{ route($role . '.materials.destroy', [
                                        'material' => $material,
                                        'as' => $role,
                                    ]) }}"
                                    method="POST"
                                    class="material-delete-form"
                                    onsubmit="return confirm('Yakin ingin menghapus materi ini?')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="material-action-btn material-action-danger"
                                    >
                                        Hapus Materi
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </article>
            @empty
                <div class="material-list-empty">
                    <div class="material-list-empty-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                            <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>
                        </svg>
                    </div>
                    <h3>Belum Ada Materi</h3>
                    <p>Belum ada materi pembelajaran yang ditambahkan ke mata kuliah ini.</p>

                    @can('create', [\App\Models\Material::class, $course])
                        <a
                            href="{{ route($role . '.courses.materials.create', [
                                'course' => $course,
                                'as' => $role,
                            ]) }}"
                            class="primary-btn material-empty-add-btn"
                        >
                            Tambah Materi Pertama
                        </a>
                    @endcan
                </div>
            @endforelse
        </div>
    </section>

    <div class="material-list-pagination">
        {{ $materials->links('vendor.pagination.kampuslms') }}
    </div>

    <div class="material-list-back">
        <a
            href="{{ route($role . '.courses.show', [
                'course' => $course,
                'as' => $role,
            ]) }}"
            class="secondary-btn"
        >
            ← Kembali ke Mata Kuliah
        </a>
    </div>
</x-layout>