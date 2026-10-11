<x-layout>
    <x-slot:title>Tambah Mata Kuliah</x-slot:title>

    <div class="course-create-header">
        <h1>Tambah Mata Kuliah</h1>
        <p>Tambahkan informasi mata kuliah baru ke dalam KampusLMS.</p>
    </div>

    <div class="course-create-card">
        <div class="course-create-card-header">
            <h2>Informasi Mata Kuliah</h2>
            <p>Silakan isi data mata kuliah sesuai kebutuhan.</p>
        </div>

        @if ($errors->any())
            <div class="form-error" role="alert">
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
                <label for="code">Kode Mata Kuliah</label>
                <input
                    id="code"
                    name="code"
                    type="text"
                    value="{{ old('code') }}"
                    maxlength="20"
                    required
                    aria-describedby="code-error"
                >
                @error('code')
                    <small id="code-error" class="form-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="name">Nama Mata Kuliah</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name') }}"
                    maxlength="255"
                    required
                    aria-describedby="name-error"
                >
                @error('name')
                    <small id="name-error" class="form-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="lecturer_id">Dosen Pengampu</label>
                <select
                    id="lecturer_id"
                    name="lecturer_id"
                    required
                    aria-describedby="lecturer-error"
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
                    <small id="lecturer-error" class="form-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="description">Deskripsi</label>
                <textarea
                    id="description"
                    name="description"
                    rows="5"
                    maxlength="2000"
                    aria-describedby="description-error"
                >{{ old('description') }}</textarea>

                @error('description')
                    <small id="description-error" class="form-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="sks">SKS</label>
                <input
                    id="sks"
                    name="sks"
                    type="number"
                    min="1"
                    max="6"
                    value="{{ old('sks', 3) }}"
                    required
                    aria-describedby="sks-error"
                >

                @error('sks')
                    <small id="sks-error" class="form-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="status">Status Mata Kuliah</label>
                <select
                    id="status"
                    name="status"
                    required
                    aria-describedby="status-error"
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
                    <small id="status-error" class="form-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-actions">
                <a
                    href="{{ route($role . '.courses.index', ['as' => $role]) }}"
                    class="cancel-button"
                >
                    Batal
                </a>

                <button type="submit" class="save-button">
                    Simpan Mata Kuliah
                </button>
            </div>
        </form>
    </div>
</x-layout>