@if ($errors->any())
    <div class="global-alert error" role="alert">
        <strong>Tugas belum dapat disimpan.</strong>

        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="form-group">
    <label for="title" class="form-label">Judul Tugas</label>

    <input
        id="title"
        name="title"
        type="text"
        class="form-input"
        value="{{ old('title', $assignment->title ?? '') }}"
        maxlength="255"
        required
        @error('title') aria-invalid="true" aria-describedby="title-error" @enderror
    >

    @error('title')
        <small id="title-error" class="form-error">{{ $message }}</small>
    @enderror
</div>

<div class="form-group">
    <label for="instructions" class="form-label">Instruksi Tugas</label>

    <textarea
        id="instructions"
        name="instructions"
        class="form-input"
        rows="6"
        required
        @error('instructions') aria-invalid="true" aria-describedby="instructions-error" @enderror
    >{{ old('instructions', $assignment->instructions ?? '') }}</textarea>

    @error('instructions')
        <small id="instructions-error" class="form-error">{{ $message }}</small>
    @enderror
</div>

<div class="form-group">
    <label for="due_at" class="form-label">Batas Pengumpulan</label>

    <input
        id="due_at"
        name="due_at"
        type="datetime-local"
        class="form-input"
        value="{{ old('due_at', isset($assignment) ? $assignment->due_at?->format('Y-m-d\TH:i') : '') }}"
        required
        @error('due_at') aria-invalid="true" aria-describedby="due-at-error" @enderror
    >

    @error('due_at')
        <small id="due-at-error" class="form-error">{{ $message }}</small>
    @enderror
</div>

<div class="form-group">
    <label for="max_score" class="form-label">Nilai Maksimal</label>

    <input
        id="max_score"
        name="max_score"
        type="number"
        class="form-input"
        min="1"
        max="100"
        value="{{ old('max_score', $assignment->max_score ?? 100) }}"
        required
        @error('max_score') aria-invalid="true" aria-describedby="max-score-error" @enderror
    >

    @error('max_score')
        <small id="max-score-error" class="form-error">{{ $message }}</small>
    @enderror
</div>

<div class="form-group">
    <label class="assignment-checkbox" for="allow_late">
        <input
            type="hidden"
            name="allow_late"
            value="0"
        >

        <input
            id="allow_late"
            name="allow_late"
            type="checkbox"
            value="1"
            @checked((bool) old('allow_late', $assignment->allow_late ?? true))
        >

        <span>
            <strong>Menerima pengumpulan terlambat</strong>
            <small>Mahasiswa tetap dapat mengumpulkan tugas setelah batas waktu jika diizinkan.</small>
        </span>
    </label>

    @error('allow_late')
        <small class="form-error">{{ $message }}</small>
    @enderror
</div>

<div class="form-group">
    <label for="status" class="form-label">Status Tugas</label>

    <select
        id="status"
        name="status"
        class="form-select"
        required
        @error('status') aria-invalid="true" aria-describedby="status-error" @enderror
    >
        <option
            value="draft"
            @selected(old('status', $assignment->status ?? 'draft') === 'draft')
        >
            Draft
        </option>

        <option
            value="published"
            @selected(old('status', $assignment->status ?? 'draft') === 'published')
        >
            Dipublikasikan
        </option>
    </select>

    @error('status')
        <small id="status-error" class="form-error">{{ $message }}</small>
    @enderror
</div>

<div class="form-actions assignment-form-actions">
    <a
        href="{{ route($role . '.courses.assignments.index', [
            'course' => $course ?? $assignment->course,
            'as' => $role,
        ]) }}"
        class="secondary-btn"
    >
        Batal
    </a>

    <button type="submit" class="primary-btn">
        Simpan Tugas
    </button>
</div>