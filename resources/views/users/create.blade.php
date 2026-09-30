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

        <form action="{{ route('admin.users.store', ['as' => $role]) }}" method="POST" class="user-form-body" novalidate>
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
                <label for="password" class="form-label">Password <span style="font-weight: normal; color: #8f9bba;">(Opsional)</span></label>
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
                <select id="user_role" name="role" class="form-select @error('role') input-error @enderror" required>
                    <option value="">-- Pilih Role --</option>
                    <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                    <option value="dosen" @selected(old('role') === 'dosen')>Dosen</option>
                    <option value="mahasiswa" @selected(old('role') === 'mahasiswa')>Mahasiswa</option>
                </select>
                @error('role')
                    <span class="error-text">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">Simpan</button>
                <a href="{{ route('admin.users.index', ['as' => $role]) }}" class="btn-cancel">Batal</a>
            </div>
        </form>
    </div>

    <style>
        .user-page-header {
            margin-bottom: 24px;
        }

        .user-page-header h1 {
            margin: 0 0 6px;
            color: #2b3674;
            font-size: 28px;
            font-weight: 700;
        }

        .user-page-header p {
            margin: 0;
            color: #8f9bba;
            font-size: 14px;
        }

        .user-form-card {
            background: #ffffff;
            border: 1px solid #e0e7ff;
            border-radius: 14px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
            overflow: hidden;
            max-width: 900px;
        }

        .user-form-header {
            padding: 24px 28px;
            border-bottom: 1px solid #f4f7fe;
        }

        .user-form-header h2 {
            margin: 0 0 6px;
            color: #2b3674;
            font-size: 18px;
            font-weight: 700;
        }

        .user-form-header p {
            margin: 0;
            color: #8f9bba;
            font-size: 13px;
        }

        .user-form-body {
            padding: 28px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-label {
            color: #2b3674;
            font-size: 13px;
            font-weight: 700;
        }

        .form-input, .form-select {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid #e0e7ff;
            border-radius: 10px;
            font-size: 14px;
            color: #2b3674;
            outline: none;
            background: #ffffff;
            box-sizing: border-box;
        }

        .form-input:focus, .form-select:focus {
            border-color: #335cff;
        }

        .input-error {
            border-color: #ee5d50;
        }

        .error-text {
            color: #ee5d50;
            font-size: 12px;
        }

        .form-actions {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 10px;
        }

        .btn-submit {
            padding: 10px 24px;
            background: #335cff;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-cancel {
            padding: 10px 20px;
            background: #f4f7fe;
            color: #2b3674;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }
    </style>
</x-layout>