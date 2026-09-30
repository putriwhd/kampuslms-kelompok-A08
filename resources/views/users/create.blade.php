<x-layout>
    <x-slot:title>Tambah Pengguna</x-slot:title>

    <div class="max-w-3xl mx-auto py-6 px-4">
        {{-- HEADER --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Tambah Pengguna</h1>
            <p class="text-sm text-gray-600">Tambahkan pengguna baru ke dalam sistem KampusLMS.</p>
        </div>

        {{-- CARD CONTAINER --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="border-b border-gray-100 pb-4 mb-6">
                <h2 class="text-lg font-semibold text-gray-700">Informasi Pengguna</h2>
                <p class="text-xs text-gray-500">Silakan isi data pengguna sesuai kebutuhan.</p>
            </div>

            <form
                action="{{ route('users.store', ['as' => $role]) }}"
                method="POST"
                class="space-y-5"
            >
                @csrf

                {{-- NAMA --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama</label>
                    <input
                        id="name"
                        name="name"
                        type="text"
                        value="{{ old('name') }}"
                        class="w-full px-3 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition @error('name') border-red-500 bg-red-50 @else border-gray-300 @enderror"
                    >
                    @error('name')
                        <small class="text-xs text-red-600 mt-1 block">{{ $message }}</small>
                    @enderror
                </div>

                {{-- EMAIL --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        value="{{ old('email') }}"
                        class="w-full px-3 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition @error('email') border-red-500 bg-red-50 @else border-gray-300 @enderror"
                    >
                    @error('email')
                        <small class="text-xs text-red-600 mt-1 block">{{ $message }}</small>
                    @enderror
                </div>

                {{-- NIM / NIP --}}
                <div>
                    <label for="nim_nip" class="block text-sm font-medium text-gray-700 mb-1">NIM/NIP</label>
                    <input
                        id="nim_nip"
                        name="nim_nip"
                        type="text"
                        value="{{ old('nim_nip') }}"
                        placeholder="Masukkan NIM atau NIP"
                        class="w-full px-3 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition @error('nim_nip') border-red-500 bg-red-50 @else border-gray-300 @enderror"
                    >
                    @error('nim_nip')
                        <small class="text-xs text-red-600 mt-1 block">{{ $message }}</small>
                    @enderror
                </div>

                {{-- PASSWORD --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        class="w-full px-3 py-2 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition @error('password') border-red-500 bg-red-50 @else border-gray-300 @enderror"
                    >
                    @error('password')
                        <small class="text-xs text-red-600 mt-1 block">{{ $message }}</small>
                    @enderror
                </div>

                {{-- ROLE --}}
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <select
                        id="role"
                        name="role"
                        class="w-full px-3 py-2 border rounded-lg shadow-sm bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 transition @error('role') border-red-500 bg-red-50 @else border-gray-300 @enderror"
                    >
                        <option value="">-- Pilih Role --</option>
                        <option value="admin" @selected(old('role', $role) === 'admin')>Admin</option>
                        <option value="dosen" @selected(old('role', $role) === 'dosen')>Dosen</option>
                        <option value="mahasiswa" @selected(old('role', $role) === 'mahasiswa')>Mahasiswa</option>
                    </select>
                    @error('role')
                        <small class="text-xs text-red-600 mt-1 block">{{ $message }}</small>
                    @enderror
                </div>

                {{-- BUTTONS --}}
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-100">
                    <a
                        href="{{ route('users.index', ['as' => $role]) }}"
                        class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
                    >
                        Batal
                    </a>

                    <button
                        type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700 transition shadow-sm"
                    >
                        Simpan Pengguna
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-layout>