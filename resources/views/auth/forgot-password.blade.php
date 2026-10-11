<x-layout>
    <x-slot:title>Lupa Kata Sandi</x-slot:title>

    <section class="login-panel">
        <h1>Lupa kata sandi</h1>
        <p>Masukkan email akun untuk menerima tautan reset.</p>

        @if (session('status'))
            <div role="status">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="login-error" role="alert">{{ $errors->first() }}</div>
        @endif

        <form action="{{ route('password.email') }}" method="POST">
            @csrf
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
            <button type="submit">Kirim tautan reset</button>
        </form>
    </section>
</x-layout>