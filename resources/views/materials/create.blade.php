<x-layout>
    <x-slot:title>Tambah Materi — {{ $course->name }}</x-slot:title>

    <section class="content-card">
        <h1>Tambah Materi Baru</h1>
        <p>Mata kuliah: <strong>{{ $course->name }}</strong></p>

        {{-- Tampilkan ringkasan error validasi --}}
        @if ($errors->any())
            <div class="form-error" role="alert">
                <strong>Materi belum dapat disimpan. Periksa kembali:</strong>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            id="material-create-form"
            action="{{ route($role . '.courses.materials.store', [
                'course' => $course,
                'as'     => $role,
            ]) }}"
            method="POST"
            enctype="multipart/form-data"
        >
            @csrf

            {{-- Judul --}}
            <div class="form-group">
                <label for="title">Judul Materi <span aria-hidden="true">*</span></label>
                <input
                    id="title"
                    name="title"
                    type="text"
                    value="{{ old('title') }}"
                    maxlength="255"
                    required
                    placeholder="Contoh: Pertemuan 1 — Pengantar Basis Data"
                >
                @error('title')
                    <small class="form-error">{{ $message }}</small>
                @enderror
            </div>

            {{-- Deskripsi --}}
            <div class="form-group">
                <label for="description">Deskripsi <span aria-hidden="true">*</span></label>
                <textarea
                    id="description"
                    name="description"
                    rows="4"
                    required
                    placeholder="Tuliskan deskripsi singkat tentang materi ini…"
                >{{ old('description') }}</textarea>
                @error('description')
                    <small class="form-error">{{ $message }}</small>
                @enderror
            </div>

            {{-- Pilihan jenis materi: Radio Dinamis --}}
            <div class="form-group">
                <fieldset>
                    <legend><strong>Jenis Materi <span aria-hidden="true">*</span></strong></legend>

                    <div style="display:flex;gap:1.5rem;margin-top:0.5rem;flex-wrap:wrap;">
                        {{-- Opsi 1: Unggah Berkas --}}
                        <label
                            id="label-type-file"
                            for="type-file"
                            style="display:inline-flex;align-items:center;gap:0.5rem;cursor:pointer;padding:0.5rem 1rem;border:2px solid #ccc;border-radius:8px;transition:all .2s;"
                        >
                            <input
                                id="type-file"
                                type="radio"
                                name="type"
                                value="file"
                                {{ old('type', 'file') === 'file' ? 'checked' : '' }}
                                onchange="toggleMaterialType()"
                            >
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                            <span>Unggah Berkas <small>(PDF / PPTX / DOCX, maks. 20 MB)</small></span>
                        </label>

                        {{-- Opsi 2: Tautan Eksternal --}}
                        <label
                            id="label-type-link"
                            for="type-link"
                            style="display:inline-flex;align-items:center;gap:0.5rem;cursor:pointer;padding:0.5rem 1rem;border:2px solid #ccc;border-radius:8px;transition:all .2s;"
                        >
                            <input
                                id="type-link"
                                type="radio"
                                name="type"
                                value="link"
                                {{ old('type', 'file') === 'link' ? 'checked' : '' }}
                                onchange="toggleMaterialType()"
                            >
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                            <span>Tautan Eksternal <small>(URL Google Drive, YouTube, dll.)</small></span>
                        </label>
                    </div>

                    @error('type')
                        <small class="form-error">{{ $message }}</small>
                    @enderror
                </fieldset>
            </div>

            {{-- Panel: Unggah Berkas --}}
            <div id="panel-file" class="form-group">
                <label for="file">
                    Berkas Materi
                    <small style="display:block;color:#666;margin-top:.2rem;">
                        Format: PDF, PPTX, atau DOCX. Ukuran maksimal <strong>20 MB</strong>.
                    </small>
                </label>
                <input
                    id="file"
                    name="file"
                    type="file"
                    accept=".pdf,.pptx,.docx,application/pdf,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                >
                @error('file')
                    <small class="form-error">{{ $message }}</small>
                @enderror
            </div>

            {{-- Panel: Tautan Eksternal --}}
            <div id="panel-link" class="form-group" style="display:none;">
                <label for="external_url">
                    URL Tautan Eksternal
                    <small style="display:block;color:#666;margin-top:.2rem;">
                        Contoh: https://drive.google.com/… atau https://youtu.be/…
                    </small>
                </label>
                <input
                    id="external_url"
                    name="external_url"
                    type="url"
                    value="{{ old('external_url') }}"
                    maxlength="255"
                    placeholder="https://…"
                >
                @error('external_url')
                    <small class="form-error">{{ $message }}</small>
                @enderror
            </div>

            {{-- Tombol Aksi --}}
            <div class="form-actions">
                <a href="{{ route($role . '.courses.materials.index', [
                    'course' => $course,
                    'as'     => $role,
                ]) }}">Batal</a>
                <button type="submit" id="btn-submit-material">
                    Simpan Materi
                </button>
            </div>
        </form>
    </section>

    {{-- Script untuk toggle panel berkas/tautan --}}
    <script>
        function toggleMaterialType() {
            const isFile = document.getElementById('type-file').checked;

            const panelFile = document.getElementById('panel-file');
            const panelLink = document.getElementById('panel-link');
            const labelFile = document.getElementById('label-type-file');
            const labelLink = document.getElementById('label-type-link');

            panelFile.style.display = isFile ? '' : 'none';
            panelLink.style.display = isFile ? 'none' : '';

            // Highlight pilihan aktif
            labelFile.style.borderColor = isFile ? 'var(--color-primary, #2563eb)' : '#ccc';
            labelFile.style.backgroundColor = isFile ? 'rgba(37,99,235,.07)' : '';
            labelLink.style.borderColor = isFile ? '#ccc' : 'var(--color-primary, #2563eb)';
            labelLink.style.backgroundColor = isFile ? '' : 'rgba(37,99,235,.07)';

            // Atur required/tidak agar validasi HTML5 tidak bertentangan
            document.getElementById('file').required = isFile;
            document.getElementById('external_url').required = !isFile;
        }

        // Jalankan saat halaman dimuat (restore state dari old input saat validasi gagal)
        document.addEventListener('DOMContentLoaded', toggleMaterialType);
    </script>
</x-layout>
