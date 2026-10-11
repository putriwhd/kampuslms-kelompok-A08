<x-layout>
    <x-slot:title>Atur Ulang Kata Sandi</x-slot:title>

    <section class="login-panel">
        <h1>Atur ulang kata sandi</h1>

        @if ($errors->any())
            <div class="login-error" role="alert">{{ $errors->first() }}</div>
        @endif

        <form action="{{ route('password.update') }}" method="POST">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">

            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" required autofocus>

            <label for="password">Kata sandi baru</label>
            <input id="password" name="password" type="password" required autocomplete="new-password">

            <label for="password_confirmation">Konfirmasi kata sandi</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">

            <button type="submit">Simpan kata sandi baru</button>
        </form>
    </section>
</x-layout>