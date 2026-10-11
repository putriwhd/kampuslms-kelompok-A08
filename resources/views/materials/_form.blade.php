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
    <select id="type" name="type" required>
        <option value="link" @selected(old('type', $material->type ?? 'link') === 'link')>
            Tautan
        </option>
        <option value="file" @selected(old('type', $material->type ?? 'link') === 'file')>
            Berkas (metadata saja)
        </option>
    </select>
    <small>Unggah berkas belum termasuk deliverable Minggu 7.</small>
    @error('type') <small class="form-error">{{ $message }}</small> @enderror
</div>

<div class="form-group">
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

<div class="form-actions">
    <a href="{{ route($role . '.courses.materials.index', [
        'course' => $course ?? $material->course,
        'as' => $role,
    ]) }}">Batal</a>
    <button type="submit">Simpan Materi</button>
</div>
