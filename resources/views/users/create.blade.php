<x-layout>
    <x-slot:title>Tambah Pengguna</x-slot:title>

    <div class="p-6">
        <h1 class="text-2xl font-bold mb-6">Tambah Pengguna</h1>

        <form action="{{ route('users.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label for="name" class="block mb-2">
                    Nama
                </label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    class="border rounded p-2 w-full"
                    placeholder="Masukkan nama pengguna"
                    required
                >

                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="email" class="block mb-2">
                    Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    class="border rounded p-2 w-full"
                    placeholder="Masukkan email"
                    required
                >

                @error('email')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="nim_nip" class="block mb-2">
                    NIM/NIP
                </label>

                <input
                    type="text"
                    id="nim_nip"
                    name="nim_nip"
                    value="{{ old('nim_nip') }}"
                    class="border rounded p-2 w-full"
                    placeholder="Masukkan NIM atau NIP"
                >

                @error('nim_nip')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="password" class="block mb-2">
                    Password
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    class="border rounded p-2 w-full"
                    placeholder="Masukkan password"
                    required
                >

                @error('password')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label for="role" class="block mb-2">
                    Role
                </label>

                <select
                    id="role"
                    name="role"
                    class="border rounded p-2 w-full"
                    required
                >
                    <option value="">-- Pilih Role --</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>
                        Admin
                    </option>
                    <option value="dosen" {{ old('role') === 'dosen' ? 'selected' : '' }}>
                        Dosen
                    </option>
                    <option value="mahasiswa" {{ old('role') === 'mahasiswa' ? 'selected' : '' }}>
                        Mahasiswa
                    </option>
                </select>

                @error('role')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button
                type="submit"
                class="border rounded px-4 py-2"
            >
                Simpan
            </button>

            <a
                href="{{ route('users.index', ['as' => request('as', 'admin')]) }}"
                class="border rounded px-4 py-2 ml-2"
            >
                Batal
            </a>
        </form>
    </div>
</x-layout>