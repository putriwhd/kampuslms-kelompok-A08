<x-layout>
    <x-slot:title>Tambah Materi — {{ $course->name }}</x-slot:title>

    <div class="page-header">
        <h1 class="page-title">Tambah Materi Baru</h1>
        <p class="page-lead">
            Tambahkan materi pembelajaran untuk mata kuliah
            <strong>{{ $course->name }}</strong>.
        </p>
    </div>

    <section class="content-card material-create-card">
        <header class="material-create-header">
            <div class="material-create-header-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg"
                     width="25" height="25" viewBox="0 0 24 24"
                     fill="none" stroke="currentColor" stroke-width="1.8"
                     stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <path d="M14 2v6h6"/>
                    <path d="M12 18v-6"/>
                    <path d="M9 15h6"/>
                </svg>
            </div>
            <div>
                <h2>Informasi Materi</h2>
                <p>Lengkapi informasi materi sebelum menyimpannya.</p>
            </div>
        </header>

        @if ($errors->any())
            <div class="form-error material-form-error" role="alert">
                <strong>Materi belum dapat disimpan.</strong>
                <p>Periksa kembali data yang Anda masukkan:</p>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            id="material-create-form"
            action="{{ route($role . '.courses.materials.store', [
                'course' => $course,
                'as' => $role,
            ]) }}"
            method="POST"
            enctype="multipart/form-data"
            class="material-create-form"
        >
            @csrf

            <div class="form-group material-form-group">
                <label for="title" class="form-label">
                    Judul Materi <span class="material-required">*</span>
                </label>
                <input
                    id="title"
                    name="title"
                    type="text"
                    class="form-input"
                    value="{{ old('title') }}"
                    maxlength="255"
                    required
                    placeholder="Contoh: Pertemuan 1 — Pengantar Basis Data"
                    autocomplete="off"
                    @if ($errors->has('title')) aria-invalid="true" @endif
                >
                @error('title')
                    <small class="material-field-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group material-form-group">
                <label for="description" class="form-label">
                    Deskripsi <span class="material-required">*</span>
                </label>
                <textarea
                    id="description"
                    name="description"
                    class="form-input material-description-input"
                    rows="4"
                    required
                    placeholder="Tuliskan deskripsi singkat tentang materi ini..."
                    @if ($errors->has('description')) aria-invalid="true" @endif
                >{{ old('description') }}</textarea>
                @error('description')
                    <small class="material-field-error">{{ $message }}</small>
                @enderror
            </div>

            <fieldset class="material-type-fieldset">
                <legend class="form-label">
                    Jenis Materi <span class="material-required">*</span>
                </legend>

                <div class="material-type-options">
                    <label
                        id="label-type-file"
                        for="type-file"
                        class="material-type-option"
                    >
                        <input
                            id="type-file"
                            type="radio"
                            name="type"
                            value="file"
                            @checked(old('type', 'file') === 'file')
                            onchange="toggleMaterialType()"
                        >
                        <span class="material-type-option-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 width="22" height="22" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="1.8"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <path d="M14 2v6h6"/>
                                <path d="M8 13h8"/>
                                <path d="M8 17h8"/>
                            </svg>
                        </span>
                        <span class="material-type-option-content">
                            <strong>Unggah Berkas</strong>
                            <small>PDF, PPTX, atau DOCX</small>
                            <small>Maksimal 20 MB</small>
                        </span>
                    </label>

                    <label
                        id="label-type-link"
                        for="type-link"
                        class="material-type-option"
                    >
                        <input
                            id="type-link"
                            type="radio"
                            name="type"
                            value="link"
                            @checked(old('type', 'file') === 'link')
                            onchange="toggleMaterialType()"
                        >
                        <span class="material-type-option-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg"
                                 width="22" height="22" viewBox="0 0 24 24"
                                 fill="none" stroke="currentColor" stroke-width="1.8"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
                            </svg>
                        </span>
                        <span class="material-type-option-content">
                            <strong>Tautan Eksternal</strong>
                            <small>Google Drive, YouTube, dan lainnya</small>
                            <small>Gunakan URL yang valid</small>
                        </span>
                    </label>
                </div>

                @error('type')
                    <small class="material-field-error">{{ $message }}</small>
                @enderror
            </fieldset>

            <div id="panel-file" class="material-type-panel">
                <div class="form-group material-form-group">
                    <label for="file" class="form-label">
                        Berkas Materi <span class="material-required">*</span>
                    </label>
                    <p class="material-field-hint">
                        Pilih berkas PDF, PPTX, atau DOCX dengan ukuran maksimal 20 MB.
                    </p>
                    <input
                        id="file"
                        name="file"
                        type="file"
                        class="form-input material-file-input"
                        accept=".pdf,.pptx,.docx,application/pdf,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                        @if ($errors->has('file')) aria-invalid="true" @endif
                    >
                    @error('file')
                        <small class="material-field-error">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div id="panel-link" class="material-type-panel" hidden>
                <div class="form-group material-form-group">
                    <label for="external_url" class="form-label">
                        URL Materi <span class="material-required">*</span>
                    </label>
                    <input
                        id="external_url"
                        name="external_url"
                        type="url"
                        class="form-input"
                        value="{{ old('external_url') }}"
                        maxlength="255"
                        placeholder="https://drive.google.com/..."
                        @if ($errors->has('external_url')) aria-invalid="true" @endif
                    >
                    <small class="material-field-hint">
                        Masukkan tautan lengkap yang dapat diakses mahasiswa.
                    </small>
                    @error('external_url')
                        <small class="material-field-error">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="form-actions material-form-actions">
                <a
                    href="{{ route($role . '.courses.materials.index', [
                        'course' => $course,
                        'as' => $role,
                    ]) }}"
                    class="secondary-btn"
                >
                    Batal
                </a>

                <button type="submit" id="btn-submit-material" class="primary-btn">
                    Simpan Materi
                </button>
            </div>
        </form>
    </section>

    <script>
        function toggleMaterialType() {
            const fileRadio = document.getElementById('type-file');
            const linkRadio = document.getElementById('type-link');
            const filePanel = document.getElementById('panel-file');
            const linkPanel = document.getElementById('panel-link');
            const fileInput = document.getElementById('file');
            const urlInput = document.getElementById('external_url');
            const fileLabel = document.getElementById('label-type-file');
            const linkLabel = document.getElementById('label-type-link');

            if (!fileRadio || !linkRadio || !filePanel || !linkPanel) {
                return;
            }

            const isFile = fileRadio.checked;

            filePanel.hidden = !isFile;
            linkPanel.hidden = !linkRadio.checked;

            fileInput.disabled = !isFile;
            fileInput.required = isFile;

            urlInput.disabled = !linkRadio.checked;
            urlInput.required = linkRadio.checked;

            fileLabel.classList.toggle('is-selected', isFile);
            linkLabel.classList.toggle('is-selected', linkRadio.checked);
        }

        toggleMaterialType();
    </script>
</x-layout>