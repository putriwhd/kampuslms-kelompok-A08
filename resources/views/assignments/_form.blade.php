@if ($errors->any())
    <div class="form-error" role="alert">
        <strong>Tugas belum dapat disimpan.</strong>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="form-group">
    <label for="title">Judul tugas</label>
    <input
        id="title"
        name="title"
        type="text"
        value="{{ old('title', $assignment->title ?? '') }}"
        maxlength="255"
        required
    >
    @error('title') <small class="form-error">{{ $message }}</small> @enderror
</div>

<div class="form-group">
    <label for="instructions">Instruksi</label>
    <textarea
        id="instructions"
        name="instructions"
        rows="6"
        required
    >{{ old('instructions', $assignment->instructions ?? '') }}</textarea>
    @error('instructions') <small class="form-error">{{ $message }}</small> @enderror
</div>

<div class="form-group">
    <label for="due_at">Batas pengumpulan</label>
    <input
        id="due_at"
        name="due_at"
        type="datetime-local"
        value="{{ old('due_at', isset($assignment) ? $assignment->due_at?->format('Y-m-d\TH:i') : '') }}"
        required
    >
    @error('due_at') <small class="form-error">{{ $message }}</small> @enderror
</div>

<div class="form-group">
    <label for="max_score">Nilai maksimal</label>
    <input
        id="max_score"
        name="max_score"
        type="number"
        min="1"
        max="100"
        value="{{ old('max_score', $assignment->max_score ?? 100) }}"
        required
    >
    @error('max_score') <small class="form-error">{{ $message }}</small> @enderror
</div>

<div class="form-group">
    <input type="hidden" name="allow_late" value="0">
    <label for="allow_late">
        <input
            id="allow_late"
            name="allow_late"
            type="checkbox"
            value="1"
            @checked(old('allow_late', $assignment->allow_late ?? true))
        >
        Menerima pengumpulan terlambat
    </label>
    @error('allow_late') <small class="form-error">{{ $message }}</small> @enderror
</div>

<div class="form-group">
    <label for="status">Status tugas</label>
    <select id="status" name="status" required>
        <option value="draft" @selected(old('status', $assignment->status ?? 'draft') === 'draft')>
            Draft
        </option>
        <option value="published" @selected(old('status', $assignment->status ?? 'draft') === 'published')>
            Published
        </option>
    </select>
    @error('status') <small class="form-error">{{ $message }}</small> @enderror
</div>

<div class="form-actions">
    <a href="{{ route($role . '.courses.assignments.index', [
        'course' => $course ?? $assignment->course,
        'as' => $role,
    ]) }}">Batal</a>
    <button type="submit">Simpan Tugas</button>
</div>
