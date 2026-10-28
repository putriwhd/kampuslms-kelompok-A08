<x-layout>
    <x-slot:title>Tambah Mata Kuliah</x-slot:title>

    <div class="page-header">
        <h1 class="page-title">Tambah Mata Kuliah</h1>
        <p class="page-lead">
            Tambahkan informasi mata kuliah baru ke dalam KampusLMS.
        </p>
    </div>

    <section class="content-card course-create-card">
        <div class="course-create-card-header">
            <h2>Informasi Mata Kuliah</h2>
            <p>Lengkapi data mata kuliah dengan benar sebelum disimpan.</p>
        </div>

        @if ($errors->any())
            <div class="form-error course-create-error" role="alert">
                <strong>Data belum dapat disimpan.</strong>
                <p>Periksa kembali isian berikut:</p>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            action="{{ route($role . '.courses.store', ['as' => $role]) }}"
            method="POST"
            class="course-create-form"
        >
            @csrf

            <div class="form-group">
                <label for="code" class="form-label">Kode Mata Kuliah</label>
                <input
                    id="code"
                    name="code"
                    type="text"
                    class="form-input"
                    value="{{ old('code') }}"
                    maxlength="20"
                    placeholder="Contoh: IF101"
                    required
                    @if ($errors->has('code')) aria-invalid="true" @endif
                >
                @error('code')
                    <small id="code-error" class="course-field-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="name" class="form-label">Nama Mata Kuliah</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    class="form-input"
                    value="{{ old('name') }}"
                    maxlength="255"
                    placeholder="Masukkan nama mata kuliah"
                    required
                    @if ($errors->has('name')) aria-invalid="true" @endif
                >
                @error('name')
                    <small id="name-error" class="course-field-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="lecturer_id" class="form-label">Dosen Pengampu</label>
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
                            @selected((string) old('lecturer_id') === (string) $lecturer->id)
                        >
                            {{ $lecturer->name }}
                        </option>
                    @endforeach
                </select>

                @error('lecturer_id')
                    <small id="lecturer-error" class="course-field-error">{{ $message }}</small>
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
                >{{ old('description') }}</textarea>

                @error('description')
                    <small id="description-error" class="course-field-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="sks" class="form-label">Jumlah SKS</label>
                <input
                    id="sks"
                    name="sks"
                    type="number"
                    class="form-input"
                    min="1"
                    max="6"
                    value="{{ old('sks', 3) }}"
                    required
                    @if ($errors->has('sks')) aria-invalid="true" @endif
                >

                @error('sks')
                    <small id="sks-error" class="course-field-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="status" class="form-label">Status Mata Kuliah</label>
                <select
                    id="status"
                    name="status"
                    class="form-select"
                    required
                    @if ($errors->has('status')) aria-invalid="true" @endif
                >
                    <option
                        value="active"
                        @selected(old('status', 'active') === 'active')
                    >
                        Aktif
                    </option>
                    <option
                        value="draft"
                        @selected(old('status') === 'draft')
                    >
                        Draf
                    </option>
                    <option
                        value="archived"
                        @selected(old('status') === 'archived')
                    >
                        Arsip
                    </option>
                </select>

                @error('status')
                    <small id="status-error" class="course-field-error">{{ $message }}</small>
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
                    Simpan Mata Kuliah
                </button>
            </div>
        </form>
    </section>
</x-layout>