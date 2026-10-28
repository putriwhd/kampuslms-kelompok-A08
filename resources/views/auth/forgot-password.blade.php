<x-layout>
    <x-slot:title>Lupa Kata Sandi</x-slot:title>

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
                        <rect x="3" y="5" width="18" height="14" rx="2"/>
                        <path d="m3 7 9 6 9-6"/>
                    </svg>
                </div>

                <h1>Lupa Kata Sandi?</h1>

                <p>
                    Masukkan email yang terhubung dengan akunmu.
                    Kami akan mengirimkan tautan untuk mengatur ulang kata sandi.
                </p>
            </div>

            @if (session('status'))
                <div class="auth-alert auth-alert-success" role="status">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="auth-alert auth-alert-error" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <form
                action="{{ route('password.email') }}"
                method="POST"
                class="auth-form"
            >
                @csrf

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
                        autofocus
                        @if ($errors->has('email')) aria-invalid="true" @endif
                    >

                    @error('email')
                        <small class="auth-field-error">{{ $message }}</small>
                    @enderror
                </div>

                <button type="submit" class="primary-btn auth-submit-btn">
                    Kirim Tautan Reset
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