<x-layout>
    <x-slot:title>Atur Ulang Kata Sandi</x-slot:title>

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
                        <rect x="3" y="11" width="18" height="11" rx="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        <path d="m9 16 2 2 4-4"/>
                    </svg>
                </div>

                <h1>Atur Ulang Kata Sandi</h1>

                <p>
                    Buat kata sandi baru untuk akun KampusLMS Anda.
                    Gunakan kata sandi yang mudah diingat, tetapi tetap aman.
                </p>
            </div>

            @if ($errors->any())
                <div class="auth-alert auth-alert-error" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <form
                action="{{ route('password.update') }}"
                method="POST"
                class="auth-form"
            >
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div class="form-group">
                    <label for="email" class="form-label">
                        Alamat Email
                    </label>

                    <input
                        id="email"
                        name="email"
                        type="email"
                        class="form-input"
                        value="{{ old('email', $email) }}"
                        placeholder="nama@email.com"
                        autocomplete="email"
                        required
                        autofocus
                        @if ($errors->has('email')) aria-invalid="true" @endif
                    >

                    @error('email')
                        <small class="auth-field-error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        Kata Sandi Baru
                    </label>

                    <input
                        id="password"
                        name="password"
                        type="password"
                        class="form-input"
                        placeholder="Masukkan kata sandi baru"
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
                        placeholder="Ulangi kata sandi baru"
                        autocomplete="new-password"
                        required
                    >
                </div>

                <button type="submit" class="primary-btn auth-submit-btn">
                    Simpan Kata Sandi Baru
                </button>
            </form>

            <div class="auth-footer">
                <a href="{{ route('login') }}" class="auth-back-link">
                    &larr; Kembali ke halaman login
                </a>
            </div>
        </section>
    </div>
</x-layout>