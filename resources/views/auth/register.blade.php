<x-layout>
    <x-slot:title>Daftar Akun - KampusLMS</x-slot:title>

    <section class="login-panel">
        <h1>Daftar Akun Mahasiswa</h1>
        <p>Akun yang dibuat melalui halaman ini memiliki peran mahasiswa.</p>

        @if ($errors->any())
            <div class="login-error" role="alert">{{ $errors->first() }}</div>
        @endif

        <form action="{{ route('register.store') }}" method="POST">
            @csrf

            <label for="name">Nama lengkap</label>
            <input id="name" name="name" type="text" value="{{ old('name') }}" required autocomplete="name">

            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">

            <label for="nim_nip">NIM <span class="label-optional">(opsional)</span></label>
            <input id="nim_nip" name="nim_nip" type="text" value="{{ old('nim_nip') }}" autocomplete="username">

            <label for="password">Kata sandi</label>
            <input id="password" name="password" type="password" required minlength="8" autocomplete="new-password">

            <label for="password_confirmation">Konfirmasi kata sandi</label>
            <input id="password_confirmation" name="password_confirmation" type="password" required minlength="8" autocomplete="new-password">

            <button type="submit">Buat akun</button>
        </form>

        <p><a href="{{ route('login') }}">Sudah punya akun? Masuk</a></p>
    </section>
</x-layout>
