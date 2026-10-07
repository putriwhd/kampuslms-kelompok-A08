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

        <form
            action="{{ route($role . '.courses.store', ['as' => $role]) }}"
            method="POST"
            class="course-create-form"
        >
            @csrf

            {{-- 1. Kode Mata Kuliah --}}
            <div class="form-group">
                <label for="code">Kode Mata Kuliah</label>
                <input
                    id="code"
                    name="code"
                    type="text"
                    value="{{ old('code') }}"
                    required
                >
                @error('code')
                    <small class="form-error">{{ $message }}</small>
                @enderror
            </div>

            {{-- 2. Nama Mata Kuliah --}}
            <div class="form-group">
                <label for="name">Nama Mata Kuliah</label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    value="{{ old('name') }}"
                    required
                >
                @error('name')
                    <small class="form-error">{{ $message }}</small>
                @enderror
            </div>

            {{-- 3. Dosen Pengampu --}}
            <div class="form-group">
                <label for="lecturer_id">Dosen Pengampu</label>
                <select id="lecturer_id" name="lecturer_id" required>
                    <option value="">-- Pilih Dosen Pengampu --</option>

                    @foreach ($lecturers as $lecturer)
                        <option
                            value="{{ $lecturer->id }}"
                            {{ old('lecturer_id') == $lecturer->id ? 'selected' : '' }}
                        >
                            {{ $lecturer->name }}
                        </option>
                    @endforeach
                </select>

                @error('lecturer_id')
                    <small class="form-error">{{ $message }}</small>
                @enderror
            </div>

            {{-- 4. Deskripsi --}}
            <div class="form-group">
                <label for="description">Deskripsi</label>

                <textarea
                    id="description"
                    name="description"
                    rows="5"
                >{{ old('description') }}</textarea>

                @error('description')
                    <small class="form-error">{{ $message }}</small>
                @enderror
            </div>

            {{-- 5. SKS --}}
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
                >

                @error('sks')
                    <small class="form-error">{{ $message }}</small>
                @enderror
            </div>

            {{-- 6. Status Mata Kuliah --}}
            <div class="form-group">
                <label for="status">Status Mata Kuliah</label>

                <select id="status" name="status" required>
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
                    <small class="form-error">{{ $message }}</small>
                @enderror
            </div>

            {{-- BUTTON --}}
            <div class="form-actions">
                <a
                    href="{{ route($role . '.courses.index', ['as' => $role]) }}"
                    class="cancel-button"
                >
                    Batal
                </a>

                <button
                    type="submit"
                    class="save-button"
                >
                    Simpan Mata Kuliah
                </button>
            </div>

        </form>
    </div>
</x-layout>