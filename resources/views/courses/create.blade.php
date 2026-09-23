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
            action="{{ route('courses.store', ['as' => $role]) }}"
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

            {{-- 3. Dosen Pengampu (Input Teks) --}}
            <div class="form-group">
                <label for="lecturer">Dosen Pengampu</label>
                <input 
                    type="text" 
                    id="lecturer" 
                    name="lecturer" 
                    value="{{ old('lecturer') }}"
                    placeholder="Masukkan nama dosen"
                    required
                >
                @error('lecturer')
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
                 >{{ old('description') }}></textarea>
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
                    <option value="Active" @selected(old('status', 'Active') === 'Active')>
                        Aktif
                    </option>
                    <option value="Draft" @selected(old('status') === 'Draft')>
                        Draf
                    </option>
                    <option value="Archive" @selected(old('status') === 'Archive')>
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
                    href="{{ route('courses.index', ['as' => $role]) }}"
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

    <style>
        .course-create-header {
            margin-bottom: 28px;
        }

        .course-create-header h1 {
            margin: 0 0 8px;
            color: var(--blue-dark);
            font-size: 27px;
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        .course-create-header p {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.6;
        }

        .course-create-card {
            overflow: hidden;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
        }

        .course-create-card-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
        }

        .course-create-card-header h2 {
            margin: 0 0 5px;
            color: var(--blue-dark);
            font-size: 18px;
            font-weight: 700;
        }

        .course-create-card-header p {
            margin: 0;
            color: var(--muted);
            font-size: 13px;
        }

        .course-create-form {
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
            transition: var(--transition);
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
        .save-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 16px;
            border-radius: 7px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: var(--transition);
        }

        .cancel-button {
            background: #f3f4f6;
            color: #555;
        }

        .cancel-button:hover {
            background: #e5e7eb;
        }

        .save-button {
            background: var(--blue);
            color: white;
            border: none;
        }

        .save-button:hover {
            background: var(--blue-dark);
            transform: translateY(-1px);
        }

        @media (max-width: 700px) {
            .course-create-form {
                padding: 18px;
            }

            .form-actions {
                flex-direction: column-reverse;
            }

            .cancel-button,
            .save-button {
                width: 100%;
            }
        }
    </style>
</x-layout>