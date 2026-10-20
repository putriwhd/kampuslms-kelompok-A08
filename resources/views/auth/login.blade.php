<x-layout>
    <x-slot:title>Masuk ke KampusLMS</x-slot:title>

    <section class="login-panel">
        <h1>Masuk ke KampusLMS</h1>
        <p>Gunakan akun terdaftar untuk membuka fitur sesuai peran Anda.</p>

        @if ($errors->any())
            <div class="login-error" role="alert">{{ $errors->first() }}</div>
        @endif

        @if (session('status'))
            <div role="status">{{ session('status') }}</div>
        @endif

        <form action="{{ route('login.store') }}" method="POST">
            @csrf

            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>

            <label for="password">Kata sandi</label>
            <input id="password" name="password" type="password" required>

            <button type="submit">Masuk</button>
        </form>

        <p><a href="{{ route('password.request') }}">Lupa kata sandi?</a></p>
    </section>
</x-layout>