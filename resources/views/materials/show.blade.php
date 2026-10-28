<x-layout>
    <x-slot:title>{{ $material->title }}</x-slot:title>

    <div class="page-header material-detail-page-header">
        <div>
            <h1 class="page-title">Detail Materi</h1>
            <p class="page-lead">
                Informasi lengkap mengenai materi pembelajaran.
            </p>
        </div>
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

    <section class="content-card material-detail-card">
        <header class="material-detail-header">
            <div class="material-detail-icon" aria-hidden="true">
                @if ($material->type === 'file')
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <path d="M14 2v6h6"/>
                        <path d="M8 13h8"/>
                        <path d="M8 17h8"/>
                    </svg>
                @elseif ($material->type === 'link')
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
                    </svg>
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/>
                    </svg>
                @endif
            </div>

            <div class="material-detail-heading">
                <span class="material-detail-eyebrow">Materi Pembelajaran</span>
                <h2>{{ $material->title }}</h2>

                <span class="material-type-badge {{ $material->type === 'file' ? 'is-file' : ($material->type === 'link' ? 'is-link' : '') }}">
                    {{ $material->type === 'file' ? 'Berkas Materi' : ($material->type === 'link' ? 'Tautan Eksternal' : ucfirst($material->type)) }}
                </span>
            </div>
        </header>

        <div class="material-detail-section">
            <h3>Deskripsi Materi</h3>

            @if ($material->description)
                <p class="material-detail-description">{{ $material->description }}</p>
            @else
                <p class="material-detail-muted">Belum ada deskripsi untuk materi ini.</p>
            @endif
        </div>

        <div class="material-detail-section">
            <h3>Informasi Mata Kuliah</h3>

            <div class="material-detail-info-row">
                <span class="material-detail-info-label">Nama Mata Kuliah</span>
                <span class="material-detail-info-value">
                    {{ $material->course->name }}
                </span>
            </div>
        </div>

        <div class="material-detail-section">
            @if ($material->type === 'link' && $material->external_url)
                <h3>Akses Materi</h3>
                <p class="material-detail-muted">
                    Materi ini tersedia melalui tautan eksternal.
                    Klik tombol berikut untuk membukanya.
                </p>

                <a
                    href="{{ $material->external_url }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="primary-btn material-detail-main-action"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        aria-hidden="true">
                        <path d="M15 3h6v6"/>
                        <path d="M10 14 21 3"/>
                        <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                    </svg>
                    Buka Materi
                </a>

            @elseif ($material->type === 'file')
                <h3>Informasi Berkas</h3>

                @if ($material->original_name || $material->file_size)
                    <div class="material-detail-file-info">
                        @if ($material->original_name)
                            <div class="material-detail-info-row">
                                <span class="material-detail-info-label">Nama Berkas</span>
                                <span class="material-detail-info-value">
                                    {{ $material->original_name }}
                                </span>
                            </div>
                        @endif

                        @if ($material->file_size)
                            <div class="material-detail-info-row">
                                <span class="material-detail-info-label">Ukuran Berkas</span>
                                <span class="material-detail-info-value">
                                    {{ $material->formatted_file_size }}
                                </span>
                            </div>
                        @endif
                    </div>

                    @can('view', $material)
                        <a
                            href="{{ route('materials.download', [
                                'material' => $material,
                            ]) }}"
                            class="primary-btn material-detail-main-action"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17"
                                viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                aria-hidden="true">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <path d="m7 10 5 5 5-5"/>
                                <path d="M12 15V3"/>
                            </svg>
                            Unduh Berkas Materi
                        </a>
                    @endcan
                @else
                    <p class="material-detail-muted">
                        Informasi berkas belum tersedia.
                    </p>
                @endif

            @else
                <h3>Akses Materi</h3>
                <p class="material-detail-muted">
                    Informasi materi belum tersedia.
                </p>
            @endif
        </div>

        <footer class="material-detail-footer">
            <a
                href="{{ route($role . '.courses.materials.index', [
                    'course' => $material->course,
                    'as' => $role,
                ]) }}"
                class="secondary-btn material-detail-back-btn"
            >
                ← Kembali ke Daftar Materi
            </a>

            @can('update', $material)
                <a
                    href="{{ route($role . '.materials.edit', [
                        'material' => $material,
                        'as' => $role,
                    ]) }}"
                    class="primary-btn material-detail-edit-btn"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        aria-hidden="true">
                        <path d="M12 20h9"/>
                        <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4Z"/>
                    </svg>
                    Edit Materi
                </a>
            @endcan
        </footer>
    </section>
</x-layout>