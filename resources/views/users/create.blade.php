<x-layout>
    <x-slot:title>Tambah Pengguna</x-slot:title>

    @php
        $role = request('as', 'admin');
        $currentRole = $role;
    @endphp

    <div class="user-page-header">
        <div>
            <h1>Tambah Pengguna</h1>
            <p>Tambahkan data pengguna baru ke dalam sistem KampusLMS.</p>
        </div>
    </div>

    <div class="user-form-card">
        <div class="user-form-header">
            <h2>Informasi Pengguna</h2>
            <p>Silakan isi data pengguna sesuai kebutuhan.</p>
        </div>

        <form
            action="{{ route('admin.users.store', ['as' => $role]) }}"
            method="POST"
            class="user-form-body"
            novalidate
        >
            @csrf

            <div class="form-group">
                <label for="name" class="form-label">Nama</label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    class="form-input @error('name') input-error @enderror"
                    placeholder="Masukkan nama lengkap"
                    required
                >

                @error('name')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Email</label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    class="form-input @error('email') input-error @enderror"
                    placeholder="nama@example.com"
                    required
                >

                @error('email')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="nim_nip" class="form-label">NIM/NIP</label>

                <input
                    type="text"
                    id="nim_nip"
                    name="nim_nip"
                    value="{{ old('nim_nip') }}"
                    class="form-input @error('nim_nip') input-error @enderror"
                    placeholder="Masukkan NIM atau NIP"
                >

                @error('nim_nip')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="password" class="form-label">
                    Password
                    <span class="label-optional">(Opsional)</span>
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    class="form-input @error('password') input-error @enderror"
                    placeholder="Kosongkan jika ingin menggunakan password default"
                >

                @error('password')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group">
                <label for="user_role" class="form-label">Role</label>

                <select
                    id="user_role"
                    name="role"
                    class="form-select @error('role') input-error @enderror"
                    required
                >
                    <option value="">-- Pilih Role --</option>
                    <option value="admin" @selected(old('role') === 'admin')>
                        Admin
                    </option>
                    <option value="dosen" @selected(old('role') === 'dosen')>
                        Dosen
                    </option>
                    <option value="mahasiswa" @selected(old('role') === 'mahasiswa')>
                        Mahasiswa
                    </option>
                </select>

                @error('role')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">
                    Simpan
                </button>

                <a
                    href="{{ route('admin.users.index', ['as' => $role]) }}"
                    class="btn-cancel"
                >
                    Batal
                </a>
            </div>
        </form>
    </div>
</x-layout>