<x-layout>
    <x-slot:title>Tambah Pengguna</x-slot:title>

    <div class="user-create-header">
        <h1>Tambah Pengguna</h1>
        <p>Tambahkan pengguna baru ke dalam sistem KampusLMS.</p>
    </div>

    <div class="user-create-card">

        <div class="user-create-card-header">
            <h2>Informasi Pengguna</h2>
            <p>Silakan isi data pengguna sesuai kebutuhan.</p>
        </div>

        <form
            action="{{ route('users.store', ['as' => request('as', 'admin')]) }}"
            method="POST"
            class="user-create-form"
        >
            @csrf

            {{-- NAMA --}}
            <div class="form-group">
                <label for="name">Nama</label>

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


            {{-- EMAIL --}}
            <div class="form-group">
                <label for="email">Email</label>

                <input
                    id="email"
                    name="email"
                    type="email"
                    value="{{ old('email') }}"
                    required
                >

                @error('email')
                    <small class="form-error">{{ $message }}</small>
                @enderror
            </div>


            {{-- NIM / NIP --}}
            <div class="form-group">
                <label for="nim_nip">NIM/NIP</label>

                <input
                    id="nim_nip"
                    name="nim_nip"
                    type="text"
                    value="{{ old('nim_nip') }}"
                    placeholder="Masukkan NIM atau NIP"
                >

                @error('nim_nip')
                    <small class="form-error">{{ $message }}</small>
                @enderror
            </div>


            {{-- PASSWORD --}}
            <div class="form-group">
                <label for="password">Password</label>

                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                >

                @error('password')
                    <small class="form-error">{{ $message }}</small>
                @enderror
            </div>


            {{-- ROLE --}}
            <div class="form-group">
                <label for="role">Role</label>

                <select
                    id="role"
                    name="role"
                    required
                >
                    <option value="">-- Pilih Role --</option>

                    <option
                        value="admin"
                        @selected(old('role') === 'admin')
                    >
                        Admin
                    </option>

                    <option
                        value="dosen"
                        @selected(old('role') === 'dosen')
                    >
                        Dosen
                    </option>

                    <option
                        value="mahasiswa"
                        @selected(old('role') === 'mahasiswa')
                    >
                        Mahasiswa
                    </option>
                </select>

                @error('role')
                    <small class="form-error">{{ $message }}</small>
                @enderror
            </div>


            {{-- BUTTON --}}
            <div class="form-actions">

                <a
                    href="{{ route('users.index', ['as' => request('as', 'admin')]) }}"
                    class="cancel-button"
                >
                    Batal
                </a>

                <button
                    type="submit"
                    class="save-button"
                >
                    Simpan Pengguna
                </button>

            </div>

        </form>
    </div>


    <style>

        /* =========================
           HEADER
        ========================= */

        .user-create-header {
            margin-bottom: 28px;
        }

        .user-create-header h1 {
            margin: 0 0 8px;

            color: var(--blue-dark);

            font-size: 27px;
            font-weight: 700;

            letter-spacing: -0.3px;
        }

        .user-create-header p {
            margin: 0;

            color: var(--muted);

            font-size: 14px;
            line-height: 1.6;
        }


        /* =========================
           CARD
        ========================= */

        .user-create-card {
            overflow: hidden;

            background: var(--white);

            border: 1px solid var(--border);
            border-radius: var(--radius);

            box-shadow: var(--shadow);
        }


        /* =========================
           CARD HEADER
        ========================= */

        .user-create-card-header {
            padding: 20px 24px;

            border-bottom: 1px solid var(--border);
        }

        .user-create-card-header h2 {
            margin: 0 0 5px;

            color: var(--blue-dark);

            font-size: 18px;
            font-weight: 700;
        }

        .user-create-card-header p {
            margin: 0;

            color: var(--muted);

            font-size: 13px;
        }


        /* =========================
           FORM
        ========================= */

        .user-create-form {
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


        /* =========================
           INPUT & SELECT
        ========================= */

        .form-group input,
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

        .form-group select {
            cursor: pointer;
        }


        /* =========================
           FOCUS
        ========================= */

        .form-group input:focus,
        .form-group select:focus {
            border-color: var(--blue);

            box-shadow:
                0 0 0 3px
                rgba(37, 99, 235, 0.10);
        }


        /* =========================
           ERROR
        ========================= */

        .form-error {
            display: block;

            margin-top: 6px;

            color: #dc2626;

            font-size: 12px;
        }


        /* =========================
           BUTTON
        ========================= */

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


        /* BATAL */

        .cancel-button {
            background: #f3f4f6;

            color: #555;
        }

        .cancel-button:hover {
            background: #e5e7eb;
        }


        /* SIMPAN */

        .save-button {
            background: var(--blue);

            color: white;

            border: none;
        }

        .save-button:hover {
            background: var(--blue-dark);

            transform: translateY(-1px);
        }


        /* =========================
           RESPONSIVE
        ========================= */

        @media (max-width: 700px) {

            .user-create-form {
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