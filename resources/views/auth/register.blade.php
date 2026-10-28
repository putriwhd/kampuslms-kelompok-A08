<x-layout>
    <x-slot:title>Daftar Akun - KampusLMS</x-slot:title>

    <div class="auth-page">
        <section class="login-panel forgot-password-panel">
            <div class="auth-header">
                <div class="auth-icon" aria-hidden="true">
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        width="28"
                        height="28"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <circle cx="9" cy="8" r="4"/>
                        <path d="M3 21v-2a6 6 0 0 1 12 0v2"/>
                        <path d="M19 8v6"/>
                        <path d="M16 11h6"/>
                    </svg>
                </div>

                <h1>Daftar Akun Mahasiswa</h1>

                <p>
                    Buat akun baru untuk mengakses fitur pembelajaran
                    di KampusLMS. Akun yang didaftarkan melalui halaman
                    ini akan memiliki peran mahasiswa.
                </p>
            </div>

            @if ($errors->any())
                <div class="auth-alert auth-alert-error" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <form
                action="{{ route('register.store') }}"
                method="POST"
                class="auth-form"
            >
                @csrf

                <div class="form-group">
                    <label for="name" class="form-label">
                        Nama Lengkap
                    </label>

                    <input
                        id="name"
                        name="name"
                        type="text"
                        class="form-input"
                        value="{{ old('name') }}"
                        placeholder="Masukkan nama lengkap"
                        autocomplete="name"
                        required
                        autofocus
                        @if ($errors->has('name')) aria-invalid="true" @endif
                    >

                    @error('name')
                        <small class="auth-field-error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">
                        Alamat Email
                    </label>

                    <input
                        id="email"
                        name="email"
                        type="email"
                        class="form-input"
                        value="{{ old('email') }}"
                        placeholder="nama@email.com"
                        autocomplete="email"
                        required
                        @if ($errors->has('email')) aria-invalid="true" @endif
                    >

                    @error('email')
                        <small class="auth-field-error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="nim_nip" class="form-label">
                        NIM
                        <span class="label-optional">(Opsional)</span>
                    </label>

                    <input
                        id="nim_nip"
                        name="nim_nip"
                        type="text"
                        class="form-input"
                        value="{{ old('nim_nip') }}"
                        placeholder="Masukkan NIM"
                        autocomplete="username"
                        @if ($errors->has('nim_nip')) aria-invalid="true" @endif
                    >

                    @error('nim_nip')
                        <small class="auth-field-error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        Kata Sandi
                    </label>

                    <input
                        id="password"
                        name="password"
                        type="password"
                        class="form-input"
                        placeholder="Minimal 8 karakter"
                        minlength="8"
                        autocomplete="new-password"
                        required
                        @if ($errors->has('password')) aria-invalid="true" @endif
                    >

                    @error('password')
                        <small class="auth-field-error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label">
                        Konfirmasi Kata Sandi
                    </label>

                    <input
                        id="password_confirmation"
                        name="password_confirmation"
                        type="password"
                        class="form-input"
                        placeholder="Ulangi kata sandi"
                        minlength="8"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <button type="submit" class="primary-btn auth-submit-btn">
                    Buat Akun
                </button>
            </form>

            <div class="auth-footer">
                <p>Sudah punya akun?</p>
                <a href="{{ route('login') }}" class="auth-back-link">
                    Masuk ke KampusLMS
                </a>
            </div>
        </section>
    </div>
</x-layout>