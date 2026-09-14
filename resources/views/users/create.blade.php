<x-layout>
    <x-slot:title>Tambah Pengguna</x-slot:title>

    <div class="content-card">
        <h1>Tambah Pengguna</h1>

        <form action="{{ route('users.store', ['as' => request('as', 'admin')]) }}" method="POST">
            @csrf

            <p>
                <label for="name">Nama</label><br>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required>
                @error('name')
                    <br><small style="color: #b91c1c;">{{ $message }}</small>
                @enderror
            </p>

            <p>
                <label for="email">Email</label><br>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required>
                @error('email')
                    <br><small style="color: #b91c1c;">{{ $message }}</small>
                @enderror
            </p>

            <p>
                <label for="nim_nip">NIM/NIP</label><br>
                <input id="nim_nip" name="nim_nip" type="text" value="{{ old('nim_nip') }}">
                @error('nim_nip')
                    <br><small style="color: #b91c1c;">{{ $message }}</small>
                @enderror
            </p>

            <p>
                <label for="password">Password</label><br>
                <input id="password" name="password" type="password" required>
                @error('password')
                    <br><small style="color: #b91c1c;">{{ $message }}</small>
                @enderror
            </p>

            <p>
                <label for="role">Role</label><br>
                <select id="role" name="role" required>
                    <option value="">-- Pilih Role --</option>
                    <option value="admin" @selected(old('role') === 'admin')>Admin</option>
                    <option value="dosen" @selected(old('role') === 'dosen')>Dosen</option>
                    <option value="mahasiswa" @selected(old('role') === 'mahasiswa')>Mahasiswa</option>
                </select>
                @error('role')
                    <br><small style="color: #b91c1c;">{{ $message }}</small>
                @enderror
            </p>

            <button type="submit">Simpan</button>
            <a href="{{ route('users.index', ['as' => request('as', 'admin')]) }}">Batal</a>
        </form>
    </div>
</x-layout>