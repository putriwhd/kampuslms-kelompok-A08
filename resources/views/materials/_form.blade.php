@if ($errors->any())
    <div class="form-error" role="alert">
        <strong>Materi belum dapat disimpan.</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="form-group">
    <label for="title">Judul materi</label>
    <input
        id="title"
        name="title"
        type="text"
        value="{{ old('title', $material->title ?? '') }}"
        maxlength="255"
        required
    >
    @error('title') <small class="form-error">{{ $message }}</small> @enderror
</div>

<div class="form-group">
    <label for="description">Deskripsi</label>
    <textarea
        id="description"
        name="description"
        rows="5"
        required
    >{{ old('description', $material->description ?? '') }}</textarea>
    @error('description') <small class="form-error">{{ $message }}</small> @enderror
</div>

<div class="form-group">
    <label for="type">Jenis materi</label>
    <select id="type" name="type" required onchange="toggleMaterialEditType()">
        <option value="link" @selected(old('type', $material->type ?? 'link') === 'link')>
            Tautan
        </option>
        <option value="file" @selected(old('type', $material->type ?? 'link') === 'file')>
            Berkas (PDF, PPTX, DOCX; maksimal 20 MB)
        </option>
    </select>
    @error('type') <small class="form-error">{{ $message }}</small> @enderror
</div>

<div class="form-group" id="material-edit-link-panel">
    <label for="external_url">URL materi (wajib untuk jenis tautan)</label>
    <input
        id="external_url"
        name="external_url"
        type="url"
        value="{{ old('external_url', $material->external_url ?? '') }}"
        maxlength="255"
    >
    @error('external_url') <small class="form-error">{{ $message }}</small> @enderror
</div>

<div class="form-group" id="material-edit-file-panel" style="display:none;">
    <label for="file">Berkas materi</label>
    @if (!empty($material?->file_path))
        <p>Berkas saat ini: {{ $material->original_name }}</p>
        <small>Kosongkan pilihan untuk tetap menggunakan berkas saat ini.</small>
    @else
        <small>Pilih PDF, PPTX, atau DOCX. Ukuran maksimal 20 MB.</small>
    @endif
    <input
        id="file"
        name="file"
        type="file"
        accept=".pdf,.pptx,.docx"
        data-has-current-file="{{ !empty($material?->file_path) ? 'true' : 'false' }}"
    >
    @error('file') <small class="form-error">{{ $message }}</small> @enderror
</div>

<div class="form-actions">
    <a href="{{ route($role . '.courses.materials.index', [
        'course' => $course ?? $material->course,
        'as' => $role,
    ]) }}">Batal</a>
    <button type="submit">Simpan Materi</button>
</div>

<script>
    function toggleMaterialEditType() {
        const isFile = document.getElementById('type').value === 'file';
        const filePanel = document.getElementById('material-edit-file-panel');
        const linkPanel = document.getElementById('material-edit-link-panel');
        const fileInput = document.getElementById('file');
        const urlInput = document.getElementById('external_url');

        filePanel.style.display = isFile ? '' : 'none';
        linkPanel.style.display = isFile ? 'none' : '';
        fileInput.required = isFile && fileInput.dataset.hasCurrentFile !== 'true';
        urlInput.required = !isFile;
    }

    document.addEventListener('DOMContentLoaded', toggleMaterialEditType);
</script>
