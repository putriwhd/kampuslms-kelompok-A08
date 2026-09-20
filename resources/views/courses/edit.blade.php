<x-layout>
    <x-slot:title>Edit Mata Kuliah</x-slot:title>

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
            action="{{ route('courses.update', ['course' => $course->id, 'as' => request('as', 'admin')]) }}"
            method="POST"
            class="course-edit-form"
        >
            @csrf
            @method('PUT')

            {{-- Kode Mata Kuliah --}}
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

            {{-- Nama Mata Kuliah (Diubah dari 'title' ke 'name') --}}
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

            {{-- SKS (Ditambahkan karena wajib di UpdateCourseRequest) --}}
            <div class="form-group">
                <label for="sks">Jumlah SKS</label>

                <input
                    type="number"
                    id="sks"
                    name="sks"
                    min="1"
                    max="6"
                    value="{{ old('sks', $course->sks ?? 3) }}"
                    required
                >

                @error('sks')
                    <small class="form-error">{{ $message }}</small>
                @enderror
            </div>

            {{-- Dosen Pengampu (Diubah dari 'lecturer' ke 'lecturer_id') --}}
            <div class="form-group">
                <label for="lecturer_id">Dosen Pengampu</label>

                <select id="lecturer_id" name="lecturer_id" required>
                    <option value="">-- Pilih Dosen Pengampu --</option>
                    @foreach ($lecturers as $lecturer)
                        <option value="{{ $lecturer->id }}"
                            {{ old('lecturer_id', $course->lecturer_id) == $lecturer->id ? 'selected' : '' }}>
                            {{ $lecturer->name }}
                        </option>
                    @endforeach
                </select>

                @error('lecturer_id')
                    <small class="form-error">{{ $message }}</small>
                @enderror
            </div>

            {{-- Deskripsi --}}
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

            {{-- Status Mata Kuliah --}}
            <div class="form-group">
                <label for="status">Status Mata Kuliah</label>

                <select
                    id="status"
                    name="status"
                    required
                >
                    <option value="active"
                        {{ old('status', $course->status) === 'active' ? 'selected' : '' }}>
                        Active
                    </option>

                    <option value="draft"
                        {{ old('status', $course->status) === 'draft' ? 'selected' : '' }}>
                        Draft
                    </option>

                    {{-- Diubah value dari 'archive' menjadi 'archived' --}}
                    <option value="archived"
                        {{ old('status', $course->status) === 'archived' ? 'selected' : '' }}>
                        Archived
                    </option>
                </select>

                @error('status')
                    <small class="form-error">{{ $message }}</small>
                @enderror
            </div>

            {{-- Tombol --}}
            <div class="form-actions">

                <a
                    href="{{ route('courses.index', ['as' => request('as', 'admin')]) }}"
                    class="cancel-button"
                >
                    Batal
                </a>

                <button
                    type="submit"
                    class="update-button"
                >
                    Perbarui Mata Kuliah
                </button>

            </div>
        </form>
    </div>

    <style>
        .course-edit-header {
            margin-bottom: 28px;
        }

        .course-edit-header h1 {
            margin: 0 0 8px;
            color: var(--blue-dark);
            font-size: 27px;
            font-weight: 700;
        }

        .course-edit-header p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .course-edit-card {
            overflow: hidden;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .course-edit-card-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
        }

        .course-edit-card-header h2 {
            margin: 0 0 5px;
            color: var(--blue-dark);
            font-size: 18px;
            font-weight: 700;
        }

        .course-edit-card-header p {
            margin: 0;
            color: var(--muted);
            font-size: 13px;
        }

        .course-edit-form {
            padding: 24px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--blue-dark);
            font-size: 13px;
            font-weight: 600;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            box-sizing: border-box;
            padding: 11px 13px;
            background: white;
            color: var(--text);
            border: 1px solid #cbd5e1;
            border-radius: 7px;
            font-family: inherit;
            font-size: 14px;
            outline: none;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .form-group select {
            cursor: pointer;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.10);
        }

        .form-error {
            display: block;
            margin-top: 6px;
            color: #dc2626;
            font-size: 12px;
        }

        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }

        .cancel-button,
        .update-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 16px;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        .cancel-button {
            background: #f3f4f6;
            color: #555;
        }

        .update-button {
            background: var(--blue);
            color: white;
            border: none;
        }

        .cancel-button:hover {
            background: #e5e7eb;
        }

        .update-button:hover {
            background: var(--blue-dark);
        }

        @media (max-width: 700px) {
            .course-edit-form {
                padding: 18px;
            }

            .form-actions {
                flex-direction: column-reverse;
            }

            .cancel-button,
            .update-button {
                width: 100%;
            }
        }
    </style>
</x-layout>