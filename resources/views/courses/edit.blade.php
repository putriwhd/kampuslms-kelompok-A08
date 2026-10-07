<x-layout>

    <x-slot:title>
        Edit Mata Kuliah
    </x-slot:title>

    <div class="course-edit-header">
        <h1>Edit Mata Kuliah</h1>
        <p>Perbarui informasi mata kuliah yang tersedia di KampusLMS.</p>
    </div>

    <div class="course-edit-card">

        <div class="course-edit-card-header">
            <h2>Informasi Mata Kuliah</h2>
            <p>Silakan ubah data mata kuliah sesuai kebutuhan.</p>
        </div>

        <form
            action="{{ route($role . '.courses.update', [$course, 'as' => $role]) }}"
            method="POST"
            class="course-edit-form"
        >
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="code">Kode Mata Kuliah</label>

                <input
                    type="text"
                    id="code"
                    name="code"
                    value="{{ old('code', $course->code) }}"
                    required
                >

                @error('code')
                    <small class="form-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="name">Nama Mata Kuliah</label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $course->name) }}"
                    required
                >

                @error('name')
                    <small class="form-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="lecturer_id">Dosen Pengampu</label>

                <select id="lecturer_id" name="lecturer_id" required>
                    <option value="">-- Pilih Dosen Pengampu --</option>

                    @foreach ($lecturers as $lecturer)
                        <option
                            value="{{ $lecturer->id }}"
                            {{ old('lecturer_id', $course->lecturer_id) == $lecturer->id ? 'selected' : '' }}
                        >
                            {{ $lecturer->name }}
                        </option>
                    @endforeach
                </select>

                @error('lecturer_id')
                    <small class="form-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="sks">SKS</label>

                <input
                    type="number"
                    id="sks"
                    name="sks"
                    min="1"
                    max="6"
                    value="{{ old('sks', $course->sks) }}"
                    required
                >

                @error('sks')
                    <small class="form-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="description">Deskripsi</label>

                <textarea
                    id="description"
                    name="description"
                    rows="5"
                >{{ old('description', $course->description) }}</textarea>

                @error('description')
                    <small class="form-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="status">Status Mata Kuliah</label>

                <select id="status" name="status" required>
                    <option
                        value="active"
                        {{ old('status', $course->status) === 'active' ? 'selected' : '' }}
                    >
                        Aktif
                    </option>

                    <option
                        value="draft"
                        {{ old('status', $course->status) === 'draft' ? 'selected' : '' }}
                    >
                        Draft
                    </option>

                    <option
                        value="archived"
                        {{ old('status', $course->status) === 'archived' ? 'selected' : '' }}
                    >
                        Arsip
                    </option>
                </select>

                @error('status')
                    <small class="form-error">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-actions">
                <button
                    type="submit"
                    class="primary-btn"
                >
                    Simpan Perubahan
                </button>

                <a
                    href="{{ route($role . '.courses.index', ['as' => $role]) }}"
                    class="secondary-btn"
                >
                    Batal
                </a>
            </div>

        </form>
    </div>

</x-layout>