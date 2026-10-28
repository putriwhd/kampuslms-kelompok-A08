<x-layout>
    <x-slot:title>Masuk ke KampusLMS</x-slot:title>

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
                    </svg>
                </div>

                <h1>Masuk ke KampusLMS</h1>

                <p>
                    Gunakan akun terdaftar untuk mengakses fitur
                    sesuai dengan peran Anda.
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
                action="{{ route('login.store') }}"
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
                        autocomplete="username"
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
                        Kata Sandi
                    </label>

                    <input
                        id="password"
                        name="password"
                        type="password"
                        class="form-input"
                        placeholder="Masukkan kata sandi"
                        autocomplete="current-password"
                        required
                    >

                    @error('password')
                        <small class="auth-field-error">{{ $message }}</small>
                    @enderror
                </div>

                <button type="submit" class="primary-btn auth-submit-btn">
                    Masuk
                </button>
            </form>

            <div class="auth-footer">
                <a
                    href="{{ route('password.request') }}"
                    class="auth-back-link"
                >
                    Lupa kata sandi?
                </a>
            </div>
        </section>
    </div>
</x-layout>