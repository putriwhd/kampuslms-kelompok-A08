<x-layout>
    <x-slot:title>Edit Mata Kuliah</x-slot:title>

    <div class="page-header">
        <h1 class="page-title">Edit Mata Kuliah</h1>
        <p class="page-lead">
            Perbarui informasi mata kuliah yang tersedia di KampusLMS.
        </p>
    </div>

    <section class="content-card course-create-card">
        <div class="course-create-card-header">
            <h2>Informasi Mata Kuliah</h2>
            <p>Periksa dan ubah data mata kuliah sesuai kebutuhan.</p>
        </div>

        @if ($errors->any())
            <div class="form-error course-create-error" role="alert">
                <strong>Perubahan belum dapat disimpan.</strong>
                <p>Periksa kembali isian berikut:</p>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            action="{{ route($role . '.courses.update', [
                'course' => $course,
                'as' => $role,
            ]) }}"
            method="POST"
            class="course-create-form"
        >
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="code" class="form-label">Kode Mata Kuliah</label>
                <input
                    id="code"
                    name="code"
                    type="text"
                    class="form-input"
                    value="{{ old('code', $course->code) }}"
                    maxlength="20"
                    placeholder="Contoh: IF101"
                    required
                    @if ($errors->has('code')) aria-invalid="true" @endif
                >
                @error('code')
                    <small class="course-field-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="name" class="form-label">Nama Mata Kuliah</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    class="form-input"
                    value="{{ old('name', $course->name) }}"
                    maxlength="255"
                    placeholder="Masukkan nama mata kuliah"
                    required
                    @if ($errors->has('name')) aria-invalid="true" @endif
                >
                @error('name')
                    <small class="course-field-error">{{ $message }}</small>
                @enderror
            </div>

            @if ($role === 'admin')
                <div class="form-group">
                    <label for="lecturer_id" class="form-label">
                        Dosen Pengampu
                    </label>

                    <select
                        id="lecturer_id"
                        name="lecturer_id"
                        class="form-select"
                        required
                        @if ($errors->has('lecturer_id')) aria-invalid="true" @endif
                    >
                        <option value="">-- Pilih Dosen Pengampu --</option>

                        @foreach ($lecturers as $lecturer)
                            <option
                                value="{{ $lecturer->id }}"
                                @selected((string) old('lecturer_id', $course->lecturer_id) === (string) $lecturer->id)
                            >
                                {{ $lecturer->name }}
                            </option>
                        @endforeach
                    </select>

                    @error('lecturer_id')
                        <small class="course-field-error">{{ $message }}</small>
                    @enderror
                </div>
            @else
                <div class="form-group">
                    <label class="form-label">Dosen Pengampu</label>
                    <div class="course-readonly-field">
                        {{ $course->lecturer?->name ?? '-' }}
                    </div>
                </div>
            @endif

            <div class="form-group">
                <label for="sks" class="form-label">Jumlah SKS</label>
                <input
                    id="sks"
                    name="sks"
                    type="number"
                    class="form-input"
                    min="1"
                    max="6"
                    value="{{ old('sks', $course->sks) }}"
                    required
                    @if ($errors->has('sks')) aria-invalid="true" @endif
                >
                @error('sks')
                    <small class="course-field-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="description" class="form-label">Deskripsi</label>
                <textarea
                    id="description"
                    name="description"
                    class="form-input course-description-input"
                    rows="5"
                    maxlength="2000"
                    placeholder="Jelaskan gambaran singkat mata kuliah"
                    @if ($errors->has('description')) aria-invalid="true" @endif
                >{{ old('description', $course->description) }}</textarea>

                @error('description')
                    <small class="course-field-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="status" class="form-label">
                    Status Mata Kuliah
                </label>

                <select
                    id="status"
                    name="status"
                    class="form-select"
                    required
                    @if ($errors->has('status')) aria-invalid="true" @endif
                >
                    <option
                        value="active"
                        @selected(old('status', $course->status) === 'active')
                    >
                        Aktif
                    </option>

                    <option
                        value="draft"
                        @selected(old('status', $course->status) === 'draft')
                    >
                        Draf
                    </option>

                    <option
                        value="archived"
                        @selected(old('status', $course->status) === 'archived')
                    >
                        Arsip
                    </option>
                </select>

                @error('status')
                    <small class="course-field-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-actions course-create-actions">
                <a
                    href="{{ route($role . '.courses.index', ['as' => $role]) }}"
                    class="secondary-btn"
                >
                    Batal
                </a>

                <button type="submit" class="primary-btn">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </section>
</x-layout>