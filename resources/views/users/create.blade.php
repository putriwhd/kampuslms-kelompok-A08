<x-layout>
    <x-slot:title>Tambah Pengguna</x-slot:title>

    <div class="page-header user-page-header">
        <div>
            <h1 class="page-title">Tambah Pengguna</h1>
            <p class="page-lead">
                Tambahkan data pengguna baru ke dalam sistem KampusLMS.
            </p>
        </div>
    </div>

    <section class="content-card user-form-card">
        <header class="user-form-header">
            <div class="user-form-header-icon" aria-hidden="true">
                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="10" cy="7" r="4"/>
                    <path d="M20 8v6"/>
                    <path d="M23 11h-6"/>
                </svg>
            </div>

            <div>
                <h2>Informasi Pengguna</h2>
                <p>Lengkapi data pengguna sesuai dengan peran yang diberikan.</p>
            </div>
        </header>

        @if ($errors->any())
            <div class="user-form-error-summary" role="alert">
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
            action="{{ route('admin.users.store', ['as' => $role]) }}"
            method="POST"
            class="user-form-body"
        >
            @csrf

            <div class="form-group">
                <label for="name" class="form-label">Nama Lengkap</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    class="form-input @error('name') input-error @enderror"
                    placeholder="Masukkan nama lengkap"
                    autocomplete="name"
                    maxlength="255"
                    required
                    @error('name') aria-invalid="true" @enderror
                >
                @error('name')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Alamat Email</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    class="form-input @error('email') input-error @enderror"
                    placeholder="nama@example.com"
                    autocomplete="email"
                    maxlength="255"
                    required
                    @error('email') aria-invalid="true" @enderror
                >
                @error('email')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="nim_nip" class="form-label">
                    NIM/NIP
                    <span class="user-field-optional">(Opsional)</span>
                </label>
                <input
                    type="text"
                    id="nim_nip"
                    name="nim_nip"
                    value="{{ old('nim_nip') }}"
                    class="form-input @error('nim_nip') input-error @enderror"
                    placeholder="Masukkan NIM atau NIP"
                    autocomplete="off"
                    maxlength="50"
                    @error('nim_nip') aria-invalid="true" @enderror
                >
                @error('nim_nip')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-input @error('password') input-error @enderror"
                    placeholder="Buat password untuk pengguna"
                    autocomplete="new-password"
                    required
                    @error('password') aria-invalid="true" @enderror
                >
                <small class="user-field-help">
                    Gunakan password yang kuat dan tidak mudah ditebak.
                </small>
                @error('password')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="user_role" class="form-label">Peran Pengguna</label>
                <select
                    id="user_role"
                    name="role"
                    class="form-select @error('role') input-error @enderror"
                    required
                    @error('role') aria-invalid="true" @enderror
                >
                    <option value="">Pilih peran pengguna</option>
                    <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                    <option value="dosen" @selected(old('role') === 'dosen')>Dosen</option>
                    <option value="mahasiswa" @selected(old('role') === 'mahasiswa')>Mahasiswa</option>
                </select>
                @error('role')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="user-form-actions">
                <button type="submit" class="primary-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17"
                        viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        aria-hidden="true">
                        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2Z"/>
                        <path d="M17 21v-8H7v8"/>
                        <path d="M7 3v5h8"/>
                    </svg>
                    Simpan Pengguna
                </button>

                <a
                    href="{{ route('admin.users.index', ['as' => $role]) }}"
                    class="secondary-btn"
                >
                    Batal
                </a>
            </div>
        </form>
    </section>
</x-layout>