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

<div class="form-group material-form-group">
    <label for="title" class="form-label">Judul Materi</label>
    <input
        id="title"
        name="title"
        type="text"
        class="form-input"
        value="{{ old('title', $material->title ?? '') }}"
        placeholder="Masukkan judul materi"
        maxlength="255"
        required
        @if ($errors->has('title')) aria-invalid="true" @endif
    >
    @error('title')
        <small class="material-field-error">{{ $message }}</small>
    @enderror
</div>

<div class="form-group material-form-group">
    <label for="description" class="form-label">Deskripsi Materi</label>
    <textarea
        id="description"
        name="description"
        class="form-input material-description-input"
        rows="5"
        placeholder="Jelaskan isi atau tujuan materi"
        required
        @if ($errors->has('description')) aria-invalid="true" @endif
    >{{ old('description', $material->description ?? '') }}</textarea>
    @error('description')
        <small class="material-field-error">{{ $message }}</small>
    @enderror
</div>

<div class="form-group material-form-group">
    <label for="type" class="form-label">Jenis Materi</label>
    <select
        id="type"
        name="type"
        class="form-select"
        required
        onchange="toggleMaterialEditType()"
    >
        <option value="link" @selected(old('type', $material->type ?? 'link') === 'link')>
            Tautan
        </option>
        <option value="file" @selected(old('type', $material->type ?? 'link') === 'file')>
            Berkas (PDF, PPTX, DOCX)
        </option>
    </select>
    <small class="material-field-hint">
        Pilih tautan dari internet atau unggah berkas dengan ukuran maksimal 20 MB.
    </small>
    @error('type')
        <small class="material-field-error">{{ $message }}</small>
    @enderror
</div>

<div class="material-type-panel" id="material-edit-link-panel">
    <div class="form-group material-form-group">
        <label for="external_url" class="form-label">URL Materi</label>
        <input
            id="external_url"
            name="external_url"
            type="url"
            class="form-input"
            value="{{ old('external_url', $material->external_url ?? '') }}"
            placeholder="https://contoh.com/materi"
            maxlength="255"
            @if ($errors->has('external_url')) aria-invalid="true" @endif
        >
        <small class="material-field-hint">
            Masukkan alamat tautan materi yang dapat diakses mahasiswa.
        </small>
        @error('external_url')
            <small class="material-field-error">{{ $message }}</small>
        @enderror
    </div>
</div>

<div class="material-type-panel" id="material-edit-file-panel">
    <div class="form-group material-form-group">
        <label for="file" class="form-label">Berkas Materi</label>

        @if (!empty($material?->file_path))
            <div class="material-current-file">
                <span class="material-current-file-icon" aria-hidden="true">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="22"
                        height="22"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.7"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <path d="M14 2v6h6"/>
                        <path d="M8 13h8"/>
                        <path d="M8 17h8"/>
                    </svg>
                </span>
                <span class="material-current-file-info">
                    <strong>Berkas saat ini</strong>
                    <span>{{ $material->original_name ?: 'Berkas materi' }}</span>
                </span>
            </div>
            <small class="material-field-hint">
                Pilih berkas baru hanya jika ingin mengganti berkas yang sekarang.
                Kosongkan pilihan untuk mempertahankan berkas saat ini.
            </small>
        @else
            <small class="material-field-hint">
                Pilih berkas PDF, PPTX, atau DOCX dengan ukuran maksimal 20 MB.
            </small>
        @endif

        <input
            id="file"
            name="file"
            type="file"
            class="form-input material-file-input"
            accept=".pdf,.pptx,.docx"
            data-has-current-file="{{ !empty($material?->file_path) ? 'true' : 'false' }}"
            @if ($errors->has('file')) aria-invalid="true" @endif
        >

        @error('file')
            <small class="material-field-error">{{ $message }}</small>
        @enderror
    </div>
</div>

<div class="form-actions material-form-actions">
    <a
        href="{{ route($role . '.courses.materials.index', [
            'course' => $course ?? $material->course,
            'as' => $role,
        ]) }}"
        class="secondary-btn"
    >
        Batal
    </a>

    <button type="submit" class="primary-btn">
        Simpan Perubahan
    </button>
</div>

<script>
    function toggleMaterialEditType() {
        const typeInput = document.getElementById('type');
        const filePanel = document.getElementById('material-edit-file-panel');
        const linkPanel = document.getElementById('material-edit-link-panel');
        const fileInput = document.getElementById('file');
        const urlInput = document.getElementById('external_url');

        if (!typeInput || !filePanel || !linkPanel || !fileInput || !urlInput) {
            return;
        }

        const isFile = typeInput.value === 'file';
        const hasCurrentFile = fileInput.dataset.hasCurrentFile === 'true';

        filePanel.hidden = !isFile;
        linkPanel.hidden = isFile;

        fileInput.required = isFile && !hasCurrentFile;
        urlInput.required = !isFile;

        fileInput.disabled = !isFile;
        urlInput.disabled = isFile;
    }

    toggleMaterialEditType();
</script>