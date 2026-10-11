<x-layout>
    <x-slot:title>Masuk ke KampusLMS</x-slot:title>

    <section class="login-panel">
        <h1>Masuk ke KampusLMS</h1>
        <p>Gunakan akun terdaftar untuk membuka fitur sesuai peran Anda.</p>

        @if ($errors->any())
            <div class="login-error" role="alert">{{ $errors->first() }}</div>
        @endif

        <form action="{{ route('login.store') }}" method="POST">
            @csrf

            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>

            <label for="password">Kata sandi</label>
            <input id="password" name="password" type="password" required>

            <button type="submit">Masuk</button>
        </form>
    </section>

    <style>
        .login-panel {
            width: min(100%, 440px);
            margin: 48px auto;
            padding: 28px;
            background: #fff;
            border: 1px solid #d8e4f2;
            border-radius: 8px;
        }

        .login-panel h1 { margin: 0 0 8px; font-size: 24px; }
        .login-panel p { margin: 0 0 24px; color: #718096; }
        .login-panel form { display: grid; gap: 10px; }
        .login-panel input { min-width: 0; padding: 10px; border: 1px solid #cbd5e1; border-radius: 4px; }
        .login-panel button { margin-top: 8px; padding: 11px; border: 0; border-radius: 4px; background: #4f6f9f; color: #fff; cursor: pointer; }
        .login-error { margin-bottom: 16px; color: #a61b1b; }
    </style>
</x-layout>