<x-layout>
    <x-slot:title>Edit Pengguna</x-slot:title>

    <div style="margin-bottom: 28px;">
        <h1 style="margin: 0 0 8px; color: var(--blue-dark); font-size: 27px; font-weight: 700;">
            Edit Pengguna
        </h1>

        <p style="margin: 0; color: var(--muted); font-size: 14px;">
            Perbarui informasi pengguna yang terdaftar di KampusLMS.
        </p>
    </div>

    <div style="
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: var(--radius);
        box-shadow: var(--shadow);
        overflow: hidden;
    ">

        <div style="
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
        ">
            <h2 style="
                margin: 0 0 5px;
                color: var(--blue-dark);
                font-size: 18px;
                font-weight: 700;
            ">
                Informasi Pengguna
            </h2>

            <p style="
                margin: 0;
                color: var(--muted);
                font-size: 13px;
            ">
                Silakan ubah data pengguna sesuai kebutuhan.
            </p>
        </div>

        <form
            action="{{ route('users.update', [$user, 'as' => request('as', 'admin')]) }}"
            method="POST"
            style="padding: 24px;"
        >

            @csrf
            @method('PUT')

            <!-- Nama -->
            <div style="margin-bottom: 20px;">
                <label for="name"
                       style="
                           display: block;
                           margin-bottom: 8px;
                           color: var(--blue-dark);
                           font-size: 13px;
                           font-weight: 600;
                       ">
                    Nama
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name', $user->name) }}"
                    required
                    style="
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
                    "
                >

                @error('name')
                    <small style="display:block; margin-top:6px; color:#dc2626;">
                        {{ $message }}
                    </small>
                @enderror
            </div>

            <!-- Email -->
            <div style="margin-bottom: 20px;">
                <label for="email"
                       style="
                           display: block;
                           margin-bottom: 8px;
                           color: var(--blue-dark);
                           font-size: 13px;
                           font-weight: 600;
                       ">
                    Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email', $user->email) }}"
                    required
                    style="
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
                    "
                >

                @error('email')
                    <small style="display:block; margin-top:6px; color:#dc2626;">
                        {{ $message }}
                    </small>
                @enderror
            </div>

            <!-- NIM/NIP -->
            <div style="margin-bottom: 20px;">
                <label for="nim_nip"
                       style="
                           display: block;
                           margin-bottom: 8px;
                           color: var(--blue-dark);
                           font-size: 13px;
                           font-weight: 600;
                       ">
                    NIM/NIP
                </label>

                <input
                    type="text"
                    id="nim_nip"
                    name="nim_nip"
                    value="{{ old('nim_nip', $user->nim_nip) }}"
                    style="
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
                    "
                >

                @error('nim_nip')
                    <small style="display:block; margin-top:6px; color:#dc2626;">
                        {{ $message }}
                    </small>
                @enderror
            </div>

            <!-- Password -->
            <div style="margin-bottom: 20px;">
                <label for="password"
                       style="
                           display: block;
                           margin-bottom: 8px;
                           color: var(--blue-dark);
                           font-size: 13px;
                           font-weight: 600;
                       ">
                    Password Baru
                    <span style="color: var(--muted); font-weight: 400;">
                        (opsional)
                    </span>
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    style="
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
                    "
                >

                <small style="
                    display: block;
                    margin-top: 6px;
                    color: var(--muted);
                    font-size: 12px;
                ">
                    Kosongkan jika password tidak ingin diubah.
                </small>

                @error('password')
                    <small style="display:block; margin-top:6px; color:#dc2626;">
                        {{ $message }}
                    </small>
                @enderror
            </div>

            <!-- Role -->
            <div style="margin-bottom: 20px;">
                <label for="role"
                       style="
                           display: block;
                           margin-bottom: 8px;
                           color: var(--blue-dark);
                           font-size: 13px;
                           font-weight: 600;
                       ">
                    Role
                </label>

                <select
                    id="role"
                    name="role"
                    required
                    style="
                        width: 100%;
                        box-sizing: border-box;
                        padding: 11px 13px;
                        background: white;
                        color: var(--text);
                        border: 1px solid #cbd5e1;
                        border-radius: 7px;
                        font-family: inherit;
                        font-size: 14px;
                        cursor: pointer;
                    "
                >
                    <option value="mahasiswa"
                        {{ old('role', $user->role) === 'mahasiswa' ? 'selected' : '' }}>
                        Mahasiswa
                    </option>

                    <option value="dosen"
                        {{ old('role', $user->role) === 'dosen' ? 'selected' : '' }}>
                        Dosen
                    </option>

                    <option value="admin"
                        {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>
                        Admin
                    </option>
                </select>

                @error('role')
                    <small style="display:block; margin-top:6px; color:#dc2626;">
                        {{ $message }}
                    </small>
                @enderror
            </div>

            <!-- Tombol -->
            <div style="
                display: flex;
                justify-content: flex-end;
                gap: 10px;
                margin-top: 28px;
                padding-top: 20px;
                border-top: 1px solid var(--border);
            ">

                <a
                    href="{{ route('users.index', ['as' => request('as', 'admin')]) }}"
                    style="
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        padding: 10px 16px;
                        background: #f3f4f6;
                        color: #555;
                        border-radius: 7px;
                        text-decoration: none;
                        font-size: 13px;
                        font-weight: 600;
                    "
                >
                    Batal
                </a>

                <button
                    type="submit"
                    style="
                        display: inline-flex;
                        align-items: center;
                        justify-content: center;
                        padding: 10px 16px;
                        background: var(--blue);
                        color: white;
                        border: none;
                        border-radius: 7px;
                        font-size: 13px;
                        font-weight: 600;
                        cursor: pointer;
                    "
                >
                    Perbarui Pengguna
                </button>

            </div>

        </form>
    </div>
</x-layout>